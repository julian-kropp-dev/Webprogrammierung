document.addEventListener("DOMContentLoaded", function () {
    setUpProfileEditing();
    registerDeletePopup();

    const profilePicture = document.getElementById('profilePicture');
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModal = document.getElementsByClassName('close')[0];

    profilePicture.onclick = function() {
        modal.style.display = "block";
        modalImage.src = this.src;
    }

    closeModal.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
});

function registerDeletePopup() {
    const deletePostPopup = document.getElementById("delete_account_popup");

    document.getElementById("delete_account_button").addEventListener("click", function (e) {
        e.preventDefault();
        deletePostPopup.style.display = "flex";
    });

    deletePostPopup.querySelector(".close_popup").addEventListener('click', function () {
        deletePostPopup.style.display = "none";
    });
}

function setUpProfileEditing() {
    const editProfileButton = document.getElementById("edit_profile_button");
    const editProfileForm = document.getElementById("edit_profile_form");
    const profileInfo = document.getElementById("profile_info");
    const isEditingElement = document.getElementById("isEditing");
    let isEditing = isEditingElement.value === 'true';

    function toggleFormElements() {
        editProfileForm.style.display = isEditing ? 'block' : 'none';
        profileInfo.style.display = isEditing ? 'none' : 'block';
    }

    toggleFormElements();

    editProfileButton.addEventListener("click", function () {
        isEditing = !isEditing;
        toggleFormElements();
        editProfileButton.textContent = isEditing ? 'Bearbeiten beenden' : 'Profil bearbeiten';
        /* With friendly help of ChatGPT */
        fetch('./profil.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'toggle_edit_mode=1'
        }).then(response => {
            if (response.ok) {
                return response.text();
            }
        })
    });
}
