$.ajax({
    url: 'ajax/get_songs.php',
    method: 'GET',
    dataType: 'json',
    success: function(data) {
        let htmlRandom = '';
        $('#recently-played').empty();
        data.randomSongs.forEach(function(song) {
            htmlRandom += `
                <div class="card" data-song="songs/${song.path}" data-id="${song.id}" data-title="${song.title}" data-author="${song.nickname}" data-cover="${song.cover}">
                    <div class="card-image" style="background-image: url('covers/${song.cover}');">
                        <button class="play-button"><i class="fas fa-play"></i></button>
                    </div>
                    <div class="card-title">${song.title}</div>
                    <div class="card-subtitle">${song.nickname}</div>
                </div>
            `;
        });
        $('#recently-played').html(htmlRandom);

        let htmlPlaylists = '';

        data.playlists.forEach(function(playlist) {
            let playlistHtml = `
                <div class="section">
                    <h2 class="section-title" data-playlist='${playlist.id}'>${playlist.name}</h2>
                    <div class="cards-grid" id="playlist-${playlist.id}">
            `;

            playlist.songs.forEach(function(song) {
                playlistHtml += `
                    <div class="card" data-song="songs/${song.path}" data-id="${song.id}" data-title="${song.title}" data-author="${song.nickname}" data-cover="${song.cover}">
                        <div class="card-image" style="background-image: url('covers/${song.cover}');">
                            <button class="play-button"><i class="fas fa-play"></i></button>
                        </div>
                        <div class="card-title">${song.title}</div>
                        <div class="card-subtitle">${song.nickname}</div>
                    </div>
                `;
            });

            playlistHtml += `
                    </div>
                </div>
            `;

            htmlPlaylists += playlistHtml;
        });

        $('#main-section').after(htmlPlaylists);
    },
    error: function() {
        $('#recently-played').html('<p>Nie udało się załadować polecanych piosenek.</p>');
    }

});

$('#nav-home').on('click', function() {
    $('.box').hide();
    $('#main-content').show();
});