

document.addEventListener("DOMContentLoaded", async function () {
    const errorContainer = document.getElementById("error_container");
    if (errorContainer) {
        setTimeout(() => {
            errorContainer.style.display = "none";
        }, 7000);
    }

    let loading = false;
    let endOfPosts = false;

    let lastLoadedPostId = null;
    let allVisiblePosts = document.querySelectorAll('.img_container');
    if (allVisiblePosts.length > 0) {
        let lastPost = allVisiblePosts.item(allVisiblePosts.length - 1);
        let lastPostId = lastPost.querySelector(".post_id").innerHTML;
        if (lastPostId === undefined || lastPostId === null || isNaN(parseInt(lastPostId, 10))) {
            console.error("Error: Invalid post ID.");
            return;
        }
        lastLoadedPostId = parseInt(lastPostId, 10);
    } else {
        lastLoadedPostId = 0;
    }

    while (!hasHorizontalScrollbar() && !endOfPosts) {
        if (loading) continue;
        await loadMorePosts();
    }

    function hasHorizontalScrollbar() {
        return document.documentElement.scrollHeight > window.innerHeight;
    }

    window.addEventListener('scroll', function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight && !loading && !endOfPosts) {
            loadMorePosts();
        }
    });

    async function loadMorePosts() {
        loading = true;
        await fetch(`ajax/server/load_more_posts.php?offset=${lastLoadedPostId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const galleryContainer = document.querySelector('.gallery_container');
                    data.forEach(post => {
                        const postElement = document.createElement('div');
                        postElement.classList.add('img_container');
                        postElement.innerHTML = `
                            <div hidden="hidden" class="post_id">${post.post_id}</div>
                            <div class="img_header">
                                <h3 class="img_title">${post.title}</h3>
                                <h4 class="img_location">Ort: ${post.airport}</h4>
                                <div class="profile-wrapper">
                                    <a href="profil.php?user_id=${post.user_id}">
                                        <img class="profile_img" src="${post.profile_photo}" alt="Profilbild" width="50">
                                    </a>
                                    <a class="profile_name" href="profil.php?user_id=${post.user_id}">${post.username}</a>
                                </div>
                            </div>
                            <a href="beitrag.php?post_id=${post.post_id}">
                                <img class="img" src="${post.image_src}" alt="Beitrag">
                            </a>
                        `;
                        galleryContainer.appendChild(postElement);
                        lastLoadedPostId = post.post_id;
                    });
                } else {
                    endOfPosts = true;
                }
                loading = false;
            })
            .catch(error => {
                console.error('Error loading more posts:', error);
                loading = false;
            });
    }
});
