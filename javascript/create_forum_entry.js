document.addEventListener('DOMContentLoaded', function () {
    // Event listener for the search button
    document.getElementById('search_button').addEventListener('click', function () {
        let query = document.getElementById('flickr_search').value;
        const limitSearch = 10;
        let url = 'https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=' + FLICKR_API_KEY + '&text=' + query + '&format=json&nojsoncallback=1';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                document.getElementById('flickr_results').innerHTML = ''; // Clear previous results
                let photos = data.photos.photo;
                let limit = Math.min(limitSearch, photos.length);

                for (let i = 0; i < limit; i++) {
                    let photo = photos[i];
                    let photoUrl = 'https://live.staticflickr.com/' + photo.server + '/' + photo.id + '_' + photo.secret + '_q.jpg';
                    let imgTag = '<img src="' + photoUrl + '" data-fullsize="https://live.staticflickr.com/' + photo.server + '/' + photo.id + '_' + photo.secret + '.jpg" data-photo-id="' + photo.id + '" class="flickr-image" style="cursor:pointer; margin:10px;" alt="Flickr-Foto:' + photo.id + '">';
                    document.getElementById('flickr_results').innerHTML += imgTag;
                }

                // Click event for each image displayed
                let flickrImages = document.querySelectorAll('.flickr-image');
                flickrImages.forEach(function (image) {

                    image.addEventListener('click', function () {
                        // Remove highlight from previously selected image
                        let previouslySelected = document.querySelector('.flickr-image.selected');
                        if (previouslySelected) {
                            previouslySelected.classList.remove('selected');
                        }

                        // Set the value and highlight the current image
                        document.getElementById('flickr_photo_id').value = this.getAttribute('data-photo-id');
                        this.classList.add('selected');
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching Flickr data:', error);
                document.getElementById('flickr_results').innerHTML = '<p>Fehler beim Laden der Bilder.</p>';
            });
    });
    const flickrCheckbox = document.getElementById('flickr_checkbox');
    const flickrContainer = document.querySelector('.flickr_container');
    const flickrInformation = document.querySelector('.js_hidden')

    flickrInformation.style.display = 'block';

    flickrCheckbox.addEventListener('change', function () {
        if (this.checked) {
            flickrContainer.style.display = 'block';
        } else {
            flickrContainer.style.display = 'none';
        }
    });
});
