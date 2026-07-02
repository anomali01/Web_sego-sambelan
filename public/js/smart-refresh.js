/**
 * Smart Auto-Refresh with Rich Notifications
 * 
 * Polls a lightweight endpoint every N seconds.
 * Only reloads when data actually changes.
 * Shows a prominent notification banner + sound when changes detected.
 * 
 * Usage:
 *   SmartRefresh.init({ pollUrl: '/admin/poll', interval: 10 });
 */
const SmartRefresh = (() => {
    let config = {
        pollUrl: '/admin/poll',
        interval: 10,       // seconds between polls
    };
    let timerId = null;
    let lastData = null;
    let isFirstLoad = true;

    // ── Notification Sound ─────────────────────────
    function playNewOrderSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();

            // Triple ascending chime — attention grabbing
            const melody = [
                { freq: 523.25, delay: 0,   dur: 0.25, vol: 0.20 }, // C5
                { freq: 659.25, delay: 180,  dur: 0.25, vol: 0.22 }, // E5
                { freq: 783.99, delay: 360,  dur: 0.30, vol: 0.24 }, // G5
                { freq: 1046.5, delay: 540,  dur: 0.45, vol: 0.18 }, // C6 (octave up)
            ];

            melody.forEach(note => {
                setTimeout(() => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(note.freq, ctx.currentTime);
                    gain.gain.setValueAtTime(note.vol, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + note.dur);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + note.dur);
                }, note.delay);
            });

            // Second pass — softer harmony for richness
            setTimeout(() => {
                const melody2 = [
                    { freq: 783.99, delay: 0,   dur: 0.35, vol: 0.10 },
                    { freq: 987.77, delay: 200,  dur: 0.40, vol: 0.08 },
                ];
                melody2.forEach(note => {
                    setTimeout(() => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(note.freq, ctx.currentTime);
                        gain.gain.setValueAtTime(note.vol, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + note.dur);
                        osc.start(ctx.currentTime);
                        osc.stop(ctx.currentTime + note.dur);
                    }, note.delay);
                });
            }, 100);

        } catch (e) {
            // AudioContext blocked by browser autoplay policy
        }
    }

    function playUpdateSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const notes = [587.33, 783.99]; // D5 → G5
            notes.forEach((freq, i) => {
                setTimeout(() => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime);
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                }, i * 150);
            });
        } catch (e) {}
    }

    // ── Notification Banner ────────────────────────
    function showNotification(type, message, detail) {
        // Remove any existing notification
        const existing = document.getElementById('sr-notification');
        if (existing) existing.remove();

        const isNewOrder = type === 'new_order';
        const bgColor = isNewOrder ? '#065F46' : '#1E3A5F';
        const accentColor = isNewOrder ? '#10B981' : '#3B82F6';
        const icon = isNewOrder ? '🔔' : '🔄';
        const pulseClass = isNewOrder ? 'sr-pulse' : '';

        const banner = document.createElement('div');
        banner.id = 'sr-notification';
        banner.innerHTML = `
            <style>
                @keyframes sr-slide-down {
                    from { transform: translateY(-100%); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                @keyframes sr-pulse-glow {
                    0%, 100% { box-shadow: 0 4px 20px rgba(16,185,129,0.3); }
                    50% { box-shadow: 0 4px 40px rgba(16,185,129,0.6), 0 0 60px rgba(16,185,129,0.2); }
                }
                #sr-notification-inner {
                    position: fixed;
                    top: 0; left: 0; right: 0;
                    z-index: 999999;
                    background: ${bgColor};
                    color: white;
                    padding: 0;
                    animation: sr-slide-down 0.5s cubic-bezier(0.16, 1, 0.3, 1);
                    border-bottom: 3px solid ${accentColor};
                    ${isNewOrder ? 'animation: sr-slide-down 0.5s cubic-bezier(0.16, 1, 0.3, 1), sr-pulse-glow 2s ease-in-out infinite;' : ''}
                }
                #sr-notification-content {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 1rem 1.5rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    flex-wrap: wrap;
                }
                .sr-notif-left {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    flex: 1;
                    min-width: 200px;
                }
                .sr-notif-icon {
                    font-size: 1.8rem;
                    line-height: 1;
                    ${isNewOrder ? 'animation: sr-bounce 0.6s ease-in-out 0.5s;' : ''}
                }
                @keyframes sr-bounce {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.3) rotate(15deg); }
                }
                .sr-notif-text h3 {
                    margin: 0;
                    font-size: 1rem;
                    font-weight: 700;
                    color: white;
                }
                .sr-notif-text p {
                    margin: 0.15rem 0 0;
                    font-size: 0.85rem;
                    color: rgba(255,255,255,0.8);
                }
                .sr-notif-actions {
                    display: flex;
                    gap: 0.5rem;
                    align-items: center;
                }
                .sr-btn-reload {
                    background: ${accentColor};
                    color: white;
                    border: none;
                    padding: 0.5rem 1.25rem;
                    border-radius: 8px;
                    font-weight: 700;
                    font-size: 0.9rem;
                    cursor: pointer;
                    transition: transform 0.2s, box-shadow 0.2s;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                }
                .sr-btn-reload:hover {
                    transform: scale(1.05);
                    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                }
                .sr-countdown {
                    font-size: 0.8rem;
                    color: rgba(255,255,255,0.6);
                    min-width: 90px;
                    text-align: right;
                }
            </style>
            <div id="sr-notification-inner">
                <div id="sr-notification-content">
                    <div class="sr-notif-left">
                        <span class="sr-notif-icon">${icon}</span>
                        <div class="sr-notif-text">
                            <h3>${message}</h3>
                            ${detail ? `<p>${detail}</p>` : ''}
                        </div>
                    </div>
                    <div class="sr-notif-actions">
                        <span class="sr-countdown" id="sr-countdown">Refresh dalam 5 detik...</span>
                        <button class="sr-btn-reload" onclick="location.reload()">
                            🔄 Muat Ulang
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        // Countdown to auto-refresh
        let seconds = 5;
        const countdownEl = document.getElementById('sr-countdown');
        const countdownTimer = setInterval(() => {
            seconds--;
            if (countdownEl) {
                countdownEl.textContent = `Refresh dalam ${seconds} detik...`;
            }
            if (seconds <= 0) {
                clearInterval(countdownTimer);
                location.reload();
            }
        }, 1000);
    }

    // ── Detect what changed ───────────────────────
    function detectChange(oldData, newData) {
        if (!oldData || !newData) return null;

        // New order arrived (total count increased)
        if (newData.total > oldData.total) {
            return {
                type: 'new_order',
                message: '📦 Pesanan Baru Masuk!',
                detail: newData.newest_num
                    ? `Pesanan ${newData.newest_num} — segera proses pesanan ini.`
                    : `Ada ${newData.total - oldData.total} pesanan baru masuk.`,
            };
        }

        // Pending count changed (payment confirmed or new pending)
        if (newData.pending !== oldData.pending) {
            if (newData.pending > oldData.pending) {
                return {
                    type: 'new_order',
                    message: '💳 Pembayaran Baru Dikonfirmasi!',
                    detail: `${newData.pending} pesanan pending menunggu diproses.`,
                };
            }
        }

        // Delivered count changed (driver marked delivered)
        if (newData.delivered !== undefined && oldData.delivered !== undefined) {
            if (newData.delivered > oldData.delivered) {
                return {
                    type: 'update',
                    message: '🚚 Driver Menyelesaikan Pengantaran!',
                    detail: 'Ada pesanan yang sudah sampai dan menunggu verifikasi Anda.',
                };
            }
        }

        // Delivering count changed
        if (newData.delivering !== undefined && oldData.delivering !== undefined) {
            if (newData.delivering !== oldData.delivering) {
                return {
                    type: 'update',
                    message: '🛵 Status Pengiriman Berubah',
                    detail: `${newData.delivering} pesanan sedang dalam pengiriman.`,
                };
            }
        }

        // Generic status change
        if (newData.latest_at !== oldData.latest_at) {
            return {
                type: 'update',
                message: '🔄 Ada Pembaruan Pesanan',
                detail: 'Status pesanan telah berubah.',
            };
        }

        return null;
    }

    // ── Polling ───────────────────────────────────
    async function poll() {
        try {
            const res = await fetch(config.pollUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });

            if (!res.ok) return;

            const data = await res.json();

            if (isFirstLoad) {
                // First load — just capture current state
                lastData = data;
                isFirstLoad = false;
                return;
            }

            const change = detectChange(lastData, data);

            if (change) {
                // Stop polling — we detected a change
                stop();

                // Play sound based on type
                if (change.type === 'new_order') {
                    playNewOrderSound();
                } else {
                    playUpdateSound();
                }

                // Show notification banner
                showNotification(change.type, change.message, change.detail);
                return;
            }

            // No change — update lastData for next comparison
            lastData = data;

        } catch (e) {
            // Network error — skip this cycle
        }
    }

    // ── Public API ────────────────────────────────
    function init(options = {}) {
        Object.assign(config, options);
        isFirstLoad = true;
        lastData = null;
        poll(); // Initial state capture
        timerId = setInterval(poll, config.interval * 1000);
    }

    function stop() {
        if (timerId) {
            clearInterval(timerId);
            timerId = null;
        }
    }

    return { init, stop };
})();
