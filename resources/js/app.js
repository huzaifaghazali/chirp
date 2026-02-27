import "./bootstrap";

// Get CSRF token
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

// Toggle like on a chirp with rate limit handling
async function toggleLike(chirpId, button) {
    // Prevent double-clicks
    if (button.disabled) return;
    button.disabled = true;

    try {
        const response = await fetch(`/chirps/${chirpId}/like`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        });

        const data = await response.json();

        if (response.status === 429) {
            // Rate limited - show specific message with countdown
            showToast(
                data.message || "Too many likes. Please slow down.",
                "warning",
            );

            // Optionally disable button temporarily
            if (data.retry_after) {
                setTimeout(
                    () => {
                        button.disabled = false;
                    },
                    Math.min(data.retry_after * 1000, 30000),
                ); // Max 30s client-side
            }
            return;
        }

        if (data.success) {
            updateLikeButton(button, data.liked, data.likes_count);
            showToast(data.message, "success");

            // Update rate limit indicator if present
            if (data.rate_limit) {
                console.log(
                    `Rate limit: ${data.rate_limit.remaining}/${data.rate_limit.limit} remaining`,
                );
            }
        } else {
            showToast(data.message || "Something went wrong", "error");
        }
    } catch (error) {
        console.error("Error:", error);
        showToast("Failed to update like", "error");
    } finally {
        button.disabled = false;
    }
}

// Update like button appearance
function updateLikeButton(button, liked, count) {
    const icon = button.querySelector(".like-icon");
    const countSpan = button.querySelector(".likes-count");

    button.setAttribute("data-liked", liked ? "true" : "false");
    countSpan.textContent = count;

    if (liked) {
        button.classList.remove("text-base-content/60");
        button.classList.add("text-error");
        icon.setAttribute("fill", "currentColor");
        icon.classList.add("scale-125");
        setTimeout(() => icon.classList.remove("scale-125"), 200);
    } else {
        button.classList.remove("text-error");
        button.classList.add("text-base-content/60");
        icon.setAttribute("fill", "none");
    }
}

// Enhanced toast with warning type
function showToast(message, type = "success") {
    // Remove existing toasts
    const existing = document.querySelector(".toast-notification");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.className = `toast-notification fixed bottom-4 right-4 z-50 animate-fade-in`;

    const alertClass =
        {
            success: "alert-success",
            error: "alert-error",
            warning: "alert-warning",
        }[type] || "alert-info";

    toast.innerHTML = `
                <div class="alert ${type === "success" ? "alert-success" : "alert-error"} shadow-lg">
                    <span>${message}</span>
                </div>
            `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transition = "opacity 0.5s";
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Make functions globally available
window.toggleLike = toggleLike;
window.showToast = showToast;
