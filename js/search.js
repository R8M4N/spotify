$(document).ready(function() {
    let searchTimeout;
    
    $('#searchInput').on('input', function() {
        const query = $(this).val().trim();

        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            $('#searchresults').empty();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            searchMusic(query);
        }, 300); 
    });
    
    function searchMusic(query) {
        $.ajax({
            url: 'ajax/search.php',
            type: 'POST',
            dataType: 'json',
            data: {
                query: query
            },
            success: function(response) {
                if (response.success) {
                    displaySearchResults(response.data);
                } else {
                    $('#searchresults').html('<div class="no-results">Błąd wyszukiwania: ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Błąd AJAX:', error);
                $('#searchresults').html('<div class="no-results">Wystąpił błąd podczas wyszukiwania</div>');
            }
        });
    }
    
    function displaySearchResults(songs) {
        const $resultsContainer = $('#searchresults');
        $resultsContainer.empty();
        
        if (songs.length === 0) {
            $resultsContainer.html('<div class="no-results">Nie znaleziono wyników</div>');
            return;
        }
        
        songs.forEach(function(song, index) {
            var trackItem = $('<div class="track-item" data-song="songs/'+song.path+'" data-id="'+song.id+'" data-title="'+song.title+'" data-author="'+song.artist+'" data-cover="'+song.cover+'">' +
                '<div class="track-number">' + (index + 1) + '</div>' +
                '<div class="track-info">' +
                    '<div class="track-image" style="background-image: url(covers/' + song.cover + ')"></div>' +
                    '<div class="track-details">' +
                        '<div class="track-title">' + song.title + '</div>' +
                        '<div class="track-artist">' + song.artist + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>');
            
            $resultsContainer.append(trackItem);
        });
        
    }
    
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const query = $(this).val().trim();
            if (query.length > 0) {
                searchMusic(query);
            }
        }
    });


    $(document).on('click', '#nav-search', function() {
        $('.box').hide();
        $('#search-section').show();
    });

});