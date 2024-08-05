document.addEventListener("DOMContentLoaded", function () {
    const editButton = document.getElementById("edit_post_button");
    const editForm = document.getElementById("edit_form");
    const tagsElement = document.getElementById("tags");

    if (editButton && editForm) {
        editForm.style.display = "none";

        editButton.addEventListener("click", function () {
            if (editForm.style.display === "none") {
                editForm.style.display = "block";
                tagsElement.style.display = "none";
            } else {
                editForm.style.display = "none";
                tagsElement.style.display = "block";
            }
        });
    }

    registerDeletePopups();
    registerLikeListeners();

});

function registerDeletePopups() {

    const deletePostPopup = document.getElementById("delete_post_popup");

    if (deletePostPopup){
        document.getElementById("delete_post_button").addEventListener("click", function (e) {
            e.preventDefault();
            deletePostPopup.style.display = "flex";
        });

        deletePostPopup.querySelector(".close_popup").addEventListener('click', function () {
            deletePostPopup.style.display = "none";
        });
    }

    let deleteButtons = document.getElementsByClassName("delete_comment_button");

    for (let deleteButton of deleteButtons) {

        let form = deleteButton.closest("form");

        // Get the next sibling of the form which is the popup div
        let popup = form.nextElementSibling;

        deleteButton.addEventListener("click", function (e) {
            e.preventDefault();
            popup.style.display = "flex";
        })

        popup.querySelector(".close_popup").addEventListener("click", function () {
            popup.style.display = "none";
        })



    }


}

function registerLikeListeners() {
    let likeButton = document.getElementById("like_container")
        .getElementsByClassName("like_button").item(0);
    let likeCount = document.getElementById("like_container")
        .getElementsByClassName("like_count").item(0);
    const urlParams = new URLSearchParams(window.location.search);
    let postId = parseInt(urlParams.get("post_id"), 10);
    likeButton.addEventListener("click", function () {
        fetch('ajax/server/like_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${encodeURIComponent(postId)}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    if (data.liked) {
                        likeButton.innerHTML = `<img id="liked" alt="Likes" src="./icons/heart-solid.svg">`
                    } else {
                        likeButton.innerHTML = '<img id="not_liked" alt="Likes" src="./icons/heart-regular.svg">'
                    }
                    likeCount.innerHTML = `Gefällt ${data.like_count} Mal`
                }
            });
    })
}