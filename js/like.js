$(document).ready(function() {
    let currentSongId = null;
    let isLiked = false;
    $('#player-image').click(function() {
        const coverUrl = $(this).css('background-image').replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
        const title = $('#player-title').text().trim();
        const artist = $('#player-artist').text().trim();
        currentSongId = $(this).attr('data-songid');
        $('.box').hide();
        $('#songView').show();
        $('#songContent').hide();
        $('#songCoverLarge').css('background-image', 'url(' + coverUrl + ')');
        $('#songTitleLarge').text(title);
        $('#songArtistLarge').text(artist);
        loadSongData(currentSongId);
    });

    function loadSongData(songId) {
        $.ajax({
            url: 'ajax/check_like.php',
            method: 'POST',
            data: { song_id: songId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateLikeButton(response.is_liked);
                    $('#songContent').show();
                } else {
                    $('#songContent').show();
                    updateLikeButton(false);
                }
            },
            error: function() {
                $('#songContent').show();
                updateLikeButton(false);
            }
        });
    }

    $('#likeButton').click(function() {
        if (!currentSongId) return;
        const $btn = $(this);
        $btn.prop('disabled', true);
        const action = isLiked ? 'unlike' : 'like';

        $.ajax({
            url: 'ajax/toggle_like.php',
            method: 'POST',
            data: { 
                song_id: currentSongId,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateLikeButton(!isLiked);
                }
            },
            error: function() {
                console.error('Błąd podczas zmiany statusu polubienia');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    function updateLikeButton(liked) {
        isLiked = liked;
        const $btn = $('#likeButton');
        const $icon = $btn.find('i');
        if (liked) {
            $btn.addClass('liked');
            $icon.removeClass('far').addClass('fas');
        } else {
            $btn.removeClass('liked');
            $icon.removeClass('fas').addClass('far');
        }
    }

    $('#nav-likes').on('click', function() {
        $('.box').hide();
        $('#likes-list').show();
        
        $.ajax({
            url: 'ajax/get_liked_songs.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#likes-list').empty();
                    
                    if (response.songs.length > 0) {
                        $('#likes-list').append('<h2>Polubione piosenki</h2>');
                        var tracksContainer = $('<div class="tracks-container"></div>');
                        response.songs.forEach(function(song, index) {
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
                            
                            tracksContainer.append(trackItem);
                        });
                        $('#likes-list').append(tracksContainer);
                    } else {
                        $('#likes-list').html('<div class="no-likes"><h2>Brak polubionych piosenek</h2></div>');
                    }
                } else {
                    $('#likes-list').html('<div class="error"><h2>Błąd</h2><p>' + ('Wystąpił błąd podczas ładowania polubionych piosenek.') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Błąd AJAX:', error);
                $('#likes-list').html('<div class="error"><h2>Błąd połączenia</h2><p>Nie udało się załadować polubionych piosenek. Spróbuj ponownie.</p></div>');
            }
        });
    });

});