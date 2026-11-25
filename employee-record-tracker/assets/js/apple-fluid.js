/**
 * Apple Fluid Interface Engine (apple-fluid.js)
 * Implements WWDC "Designing Fluid Interfaces" principles:
 *  - §1 Instant pointer-down response + ~10px hysteresis (§10)
 *  - §2 1:1 Direct manipulation with setPointerCapture & grab offset
 *  - §3 Interruptible spring animation starting from live presentation value
 *  - §4 & §5 Damping/Response spring with release velocity handoff
 *  - §6 Momentum projection: (v / 1000) * d / (1 - d)
 *  - §9 Rubber-banding at top boundary
 */
(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // -------------------------------------------------------------------------
    // 1. Instant Pointer-Down Feedback with 10px Hysteresis (§1, §10)
    // -------------------------------------------------------------------------
    document.addEventListener('pointerdown', (e) => {
        const btn = e.target.closest('.btn');
        if (!btn) return;
        const startX = e.clientX;
        const startY = e.clientY;
        btn.classList.add('is-pressed');

        const onMove = (moveEvt) => {
            const dx = moveEvt.clientX - startX;
            const dy = moveEvt.clientY - startY;
            const rect = btn.getBoundingClientRect();
            const pad = 10; // 10px hysteresis padding around target
            const inside =
                moveEvt.clientX >= rect.left - pad &&
                moveEvt.clientX <= rect.right + pad &&
                moveEvt.clientY >= rect.top - pad &&
                moveEvt.clientY <= rect.bottom + pad;
            if (!inside && Math.hypot(dx, dy) > 10) {
                btn.classList.remove('is-pressed');
            } else if (inside) {
                btn.classList.add('is-pressed');
            }
        };

        const onEnd = () => {
            btn.classList.remove('is-pressed');
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup', onEnd);
            window.removeEventListener('pointercancel', onEnd);
        };

        window.addEventListener('pointermove', onMove, { passive: true });
        window.addEventListener('pointerup', onEnd, { passive: true });
        window.addEventListener('pointercancel', onEnd, { passive: true });
    });

    // -------------------------------------------------------------------------
    // 2. Apple Physics Helpers (§6 Momentum Projection, §9 Rubber-banding)
    // -------------------------------------------------------------------------
    function project(initialVelocity, decelerationRate = 0.992) {
        return ((initialVelocity / 1000) * decelerationRate) / (1 - decelerationRate);
    }

    function rubberband(overshoot, dimension, constant = 0.55) {
        return (overshoot * dimension * constant) / (dimension + constant * Math.abs(overshoot));
    }

    // -------------------------------------------------------------------------
    // 3. Interruptible Spring Animator (§3, §4, §5)
    //    Parameterized by Apple's (dampingRatio, response)
    // -------------------------------------------------------------------------
    function createSpringController(element) {
        let currentY = 0;
        let velocityY = 0;
        let rafId = null;

        function stop() {
            if (rafId !== null) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        }

        function setY(y) {
            currentY = y;
            if (Math.abs(y) < 0.05) {
                element.style.transform = '';
            } else {
                element.style.transform = `translate3d(0, ${y.toFixed(2)}px, 0)`;
            }
        }

        function animateTo(targetY, initialVelocity = 0, dampingRatio = 0.84, response = 0.32, onComplete = null) {
            stop();
            if (prefersReducedMotion) {
                setY(targetY);
                if (onComplete) onComplete();
                return;
            }

            velocityY = initialVelocity;
            const omega = (2 * Math.PI) / response; // natural frequency
            let lastTime = performance.now();

            function tick(now) {
                const dt = Math.min((now - lastTime) / 1000, 0.032);
                lastTime = now;

                const displacement = currentY - targetY;
                const springForce = -omega * omega * displacement;
                const dampingForce = -2 * dampingRatio * omega * velocityY;
                const acceleration = springForce + dampingForce;

                velocityY += acceleration * dt;
                currentY += velocityY * dt;

                setY(currentY);

                if (Math.abs(currentY - targetY) < 0.4 && Math.abs(velocityY) < 4) {
                    setY(targetY);
                    velocityY = 0;
                    rafId = null;
                    if (onComplete) onComplete();
                    return;
                }
                rafId = requestAnimationFrame(tick);
            }

            rafId = requestAnimationFrame(tick);
        }

        return {
            getY: () => currentY,
            setY,
            stop,
            animateTo
        };
    }

    // -------------------------------------------------------------------------
    // 4. Attach 1:1 Drag, Rubber-band & Flick-to-Dismiss to Modal Sheets
    // -------------------------------------------------------------------------
    document.querySelectorAll('.modal').forEach((modalEl) => {
        const surface = modalEl.querySelector('.modal-content-apple');
        if (!surface) return;

        const spring = createSpringController(surface);
        let isDragging = false;
        let grabOffsetY = 0;
        let samples = [];

        modalEl.addEventListener('show.bs.modal', (event) => {
            spring.stop();
            spring.setY(0);

            // Populate Delete Modal fields if triggered from a row button
            const button = event.relatedTarget;
            if (button && button.hasAttribute('data-emp-id')) {
                const idInput = modalEl.querySelector('#deleteEmpId');
                const nameEl = modalEl.querySelector('#deleteEmpName');
                const codeEl = modalEl.querySelector('#deleteEmpCode');
                if (idInput) idInput.value = button.getAttribute('data-emp-id');
                if (nameEl) nameEl.textContent = button.getAttribute('data-emp-name');
                if (codeEl) codeEl.textContent = 'Employee ID: ' + button.getAttribute('data-emp-code');
            }
        });

        surface.addEventListener('pointerdown', (e) => {
            // Allow dragging from the sheet handle or modal header
            const handle = e.target.closest('.sheet-grab-handle, .modal-header-draggable');
            if (!handle || e.target.closest('button, a, input')) return;

            // Interrupt any in-flight spring cleanly from live presentation value (§3)
            spring.stop();
            isDragging = true;
            surface.setPointerCapture(e.pointerId);

            // Respect grab offset relative to current Y (§2)
            grabOffsetY = e.clientY - spring.getY();
            samples = [{ y: spring.getY(), t: performance.now() }];
        });

        surface.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            const rawY = e.clientY - grabOffsetY;
            const height = surface.offsetHeight || 320;

            // Apply rubber-banding when dragging upward above 0 (§9)
            const nextY = rawY < 0 ? -rubberband(-rawY, height, 0.55) : rawY;
            spring.setY(nextY);

            const now = performance.now();
            samples.push({ y: nextY, t: now });
            // Keep last 80ms of pointer history for velocity calculation (§2, §5)
            while (samples.length > 2 && now - samples[0].t > 80) {
                samples.shift();
            }
        });

        function endDrag() {
            if (!isDragging) return;
            isDragging = false;

            // Calculate release velocity in px/s (§5)
            let releaseVelocity = 0;
            if (samples.length >= 2) {
                const first = samples[0];
                const last = samples[samples.length - 1];
                const dt = (last.t - first.t) / 1000;
                if (dt > 0.004) {
                    releaseVelocity = (last.y - first.y) / dt;
                }
            }

            // Project resting position (§6)
            const currentY = spring.getY();
            const projectedY = currentY + project(releaseVelocity, 0.99);

            if (projectedY > 140 && currentY > 25) {
                // Flicked downward: dismiss modal with momentum handoff
                spring.animateTo(380, releaseVelocity, 1.0, 0.24, () => {
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    setTimeout(() => spring.setY(0), 200);
                });
            } else {
                // Snap back to resting origin (y = 0) with slight bounce if flicked
                const damping = Math.abs(releaseVelocity) > 350 ? 0.82 : 1.0;
                spring.animateTo(0, releaseVelocity, damping, 0.34);
            }
        }

        surface.addEventListener('pointerup', endDrag);
        surface.addEventListener('pointercancel', endDrag);
    });
})();
