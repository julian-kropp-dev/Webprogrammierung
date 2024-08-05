document.addEventListener('DOMContentLoaded', function () {
    const editButton = document.querySelector('.edit_post_button');
    const postCreatorContainer = document.querySelector('.post_creator_container');
    const editForm = document.querySelector('.edit_form_container');
    const form = document.querySelector('.edit_form_container form');

    if(editForm) {editForm.style.display = 'none';}

    const deletePostForm = document.querySelector('.delete_post_form');
    const deleteButton = document.querySelector('.delete_post_button');
    const modal = document.querySelector(".modal");
    const confirmButton = document.querySelector(".confirm_delete_button");
    const cancelButton = document.querySelector(".cancel_delete_button");

    const editCommentButtons = document.querySelectorAll('.edit_comment_button');
    const deleteCommentButtons = document.querySelectorAll('.delete_comment_button');

    let isEditPostMode = false;

    // Toggles the isEditPostMode which hides/shows the edit post form.
    if (editButton) {
        editButton.addEventListener('click', function () {
            isEditPostMode = !isEditPostMode;
            if (isEditPostMode) {
                postCreatorContainer.style.display = 'none';
                editForm.style.display = 'block';
            } else {
                postCreatorContainer.style.display = 'block';
                editForm.style.display = 'none';
                form.reset();
            }
        });
    }

    if (deleteButton) {
        deleteButton.addEventListener("click", function (event) {
            event.preventDefault();
            modal.style.display = "block";
        });
    }

    confirmButton.addEventListener("click", function () {
        if (deletePostForm) {
            deletePostForm.submit();
        }
    });

    cancelButton.addEventListener("click", function () {
        modal.style.display = "none";
    });

    deleteCommentButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            modal.style.display = "block";
            const deleteForm = button.closest('form');
            confirmButton.onclick = function () {
                deleteForm.submit();
                modal.style.display = "none";
            };
            cancelButton.onclick = function () {
                modal.style.display = "none";
            };
        });
    });

    // Toggle for the edit comment form
    editCommentButtons.forEach(button => {
        const commentId = button.getAttribute('data-comment-id');
        const form = document.querySelector(`.edit_comment_form_container[data-comment-id='${commentId}']`);
        form.style.display = "none";
        const profileContainer = document.querySelector(`.profile_comment_container[data-comment-id='${commentId}']`);

        let isEditCommentMode = false;

        button.addEventListener('click', function () {
            isEditCommentMode = !isEditCommentMode;
            if (isEditCommentMode) {
                form.style.display = 'block';
                profileContainer.style.display = 'none';
            } else {
                form.style.display = 'none';
                profileContainer.style.display = 'flex';
                form.reset();
            }
        });
    });

    const flickrImageContainer = document.querySelector('#flickr_image_container');
    if (flickrImageContainer) {
        const flickrId = flickrImageContainer.getAttribute('data-flickr-id');

        if (flickrId) {

            const url = 'https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=' + FLICKR_API_KEY + '&photo_id=' + flickrId + '&format=json&nojsoncallback=1';

            $.getJSON(url, function (data) {
                if (data.stat === 'ok') {
                    const photo = data.photo;
                    const photoUrl = 'https://live.staticflickr.com/' + photo.server + '/' + photo.id + '_' + photo.secret + '.jpg';
                    const imgTag = '<img src="' + photoUrl + '" alt="Flickr Image" class="flickr-image">';
                    flickrImageContainer.innerHTML = imgTag;
                } else {
                    flickrImageContainer.innerHTML = '<p>Bild konnte nicht geladen werden.</p>';
                }
            }).fail(function () {
                flickrImageContainer.innerHTML = '<p>Fehler beim Laden des Bildes.</p>';
            });
        }
    }
});
