
/* Automatically use the selected item of the drop down menu in the search container */

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('search-by-tags').addEventListener('change', function() {
        document.getElementById('searchTagsForm').submit();
    });

    document.getElementById('sort_by').addEventListener('change', function() {
        document.getElementById('sortingForm').submit();
    });
});
