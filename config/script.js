// Oy verme butonlarını bağlayan fonksiyon (sayfa + modal için tekrar çağrılabilir)
function bindVoteButtons() {
    document.querySelectorAll(".vote-container").forEach(container => {
        const postId = container.getAttribute("data-post-id");
        const upBtn = container.querySelector(".upvote-button");
        const downBtn = container.querySelector(".downvote-button");
        const countEl = container.querySelector(".vote-count");

        if (!upBtn || !downBtn || !countEl) return;

        // Önceki eventleri engellemek için tıklama öncesi remove olabilir ama burada yeniden bağlamak yeterli

        function sendVote(voteValue) {
            fetch("vote.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `post_id=${postId}&vote=${voteValue}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    countEl.textContent = data.vote_sum;

                    if (voteValue === 1) {
                        upBtn.classList.toggle("active");
                        downBtn.classList.remove("active");
                    } else {
                        downBtn.classList.toggle("active");
                        upBtn.classList.remove("active");
                    }
                }
            });
        }

        upBtn.addEventListener("click", () => sendVote(1));
        downBtn.addEventListener("click", () => sendVote(-1));
    });
}

// Sayfa yüklendiğinde başlat
document.addEventListener("DOMContentLoaded", bindVoteButtons);


// MODAL FONKSİYONLARI

function openModal(postId) {
    const modal = document.getElementById("myModal");
    const modalContent = modal.querySelector(".card");

    fetch(`get_post.php?id=${postId}`)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            modal.style.display = "flex";
            document.body.style.overflow = 'hidden';

            // Modal içeriği geldikten sonra vote butonlarını tekrar bağla
            bindVoteButtons();
        })
        .catch(error => {
            console.error("Modal içeriği alınamadı:", error);
        });
}

function closeModal() {
    const modal = document.getElementById("myModal");
    modal.style.display = "none";
    document.body.style.overflow = '';
}

window.onclick = function(event) {
    const modal = document.getElementById("myModal");
    if (!modal) return;

    if (event.target === modal) {
        closeModal();
    }
}
