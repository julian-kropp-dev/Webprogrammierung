document.addEventListener("DOMContentLoaded", function() {
    const dragDropArea = document.getElementById("drag_drop_area");
    const fileInput = document.getElementById("file_input");

    // Prevent default behavior for drag-and-drop
    ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
        dragDropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when file is dragged over it
    ["dragenter", "dragover"].forEach(eventName => {
        dragDropArea.addEventListener(eventName, highlight, false);
    });

    ["dragleave", "drop"].forEach(eventName => {
        dragDropArea.addEventListener(eventName, unHighlight, false);
    });

    function highlight(e) {
        dragDropArea.classList.add("highlight");
    }

    function unHighlight(e) {
        dragDropArea.classList.remove("highlight");
    }


    dragDropArea.addEventListener("drop", handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        handleFiles(files);
    }

    // Handle file selection via input
    fileInput.addEventListener("change", (e) => {
        const files = e.target.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        const fileList = [...files];
        if (fileList.length > 0) {
            const file = fileList[0];
            previewFile(file);

            fileInput.files = files;

        }
    }

    function previewFile(file) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function() {
            const img = document.createElement("img");
            img.src = reader.result;
            img.style.maxWidth = "400px";

            // Clear previous preview images
            for (let img of dragDropArea.getElementsByTagName("img")) {
                dragDropArea.removeChild(img);
            }
            dragDropArea.appendChild(img);
        }
    }


    const submitButton = document.getElementById("submit_button");
    submitButton.addEventListener("click", function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert("Bitte wählen Sie ein Bild zum Hochladen aus.");
        }
    });
});
