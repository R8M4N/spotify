$(document).on('click', 'h2.section-title[data-playlist]', function() {
    $('.box').hide();
    $('#playlist-view').show();
    $('.nav-item, .playlist-item').removeClass('active');
    
    var playlistId = $(this).data('playlist');
    $.ajax({
        url: 'ajax/get_playlist.php',
        type: 'POST',
        data: {
            playlist_id: playlistId
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#playlist-view .playlist-cover').css('background-image', 'url(covers/' + response.playlist.cover + ')');
                $('#playlist-view .playlist-info h1').text(response.playlist.name);
                
                $('#playlist-view .track-list').empty();

                $.each(response.songs, function(index, song) {
                    var trackItem = $('<div class="track-item" data-song="songs/'+song.path+'" data-id="'+song.id+'"  data-title="'+song.title+'" data-author="'+song.artist+'" data-cover="'+song.cover+'">' +
                        '<div class="track-number">' + (index + 1) + '</div>' +
                        '<div class="track-info">' +
                            '<div class="track-image" style="background-image: url(covers/' + song.cover + ')"></div>' +
                            '<div class="track-details">' +
                                '<div class="track-title">' + song.title + '</div>' +
                                '<div class="track-artist">' + song.artist + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>');
                    
                    $('#playlist-view .track-list').append(trackItem);
                });
            } else {
                alert('Błąd podczas ładowania playlisty: ' + response.message);
            }
        },
        error: function() {
            alert('Wystąpił błąd podczas komunikacji z serwerem');
        }
    });
});