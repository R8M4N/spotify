 $(document).ready(function() {
    const audio = document.getElementById('audio-player');
    const playPauseBtn = $('#play-pause-btn');
    const playPauseIcon = playPauseBtn.find('i');
    const progressBar = $('#progress-bar');
    const progressFill = $('#progress-fill');
    const currentTimeSpan = $('#current-time');
    const totalTimeSpan = $('#total-time');
    const volumeBar = $('#volume-bar');
    const volumeFill = $('#volume-fill');
    const volumeBtn = $('#volume-btn');
    
    let isPlaying = false;
    let currentSong = null;
    let repeat = false;

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    function updateProgress() {
        if (audio.duration) {
            const progress = (audio.currentTime / audio.duration) * 100;
            progressFill.css('width', progress + '%');
            currentTimeSpan.text(formatTime(audio.currentTime));
            totalTimeSpan.text(formatTime(audio.duration));
        }
    }

    function playSong(songpath,songtitle,songauthor,songcover,songid) {
        if(currentSong != songpath){
            if (songid) {
                updateHistory(songid);
            }
        }
        currentSong = songpath;
        $('#player-title').text(songtitle);
        $('#player-artist').text(songauthor);
        $('#player-image').css('background-image', 'url("covers/' + songcover + '")');
        $('#player-image').attr('data-songid', songid);
        audio.src = songpath;
        audio.load();
        
        audio.play().then(() => {
            isPlaying = true;
            playPauseIcon.removeClass('fa-play').addClass('fa-pause');
            updateAllPlayButtons();
        }).catch(error => {
            isPlaying = false;
            playPauseIcon.removeClass('fa-pause').addClass('fa-play');
        });
        
    }


    function togglePlayPause() {
        if (isPlaying) {
            audio.pause();
            isPlaying = false;
            playPauseIcon.removeClass('fa-pause').addClass('fa-play');
        } else {
            if (currentSong) {
                audio.play().then(() => {
                    isPlaying = true;
                    playPauseIcon.removeClass('fa-play').addClass('fa-pause');
                }).catch(error => {
                    console.error('Error playing audio:', error);
                });
            }
        }
        updateAllPlayButtons();
    }


    function updateAllPlayButtons() {
        $('.play-button i').removeClass('fa-pause').addClass('fa-play');
        if (isPlaying && currentSong) {
            $(`[data-song="${currentSong}"] .play-button i`).removeClass('fa-play').addClass('fa-pause');
        }
    }


    playPauseBtn.click(togglePlayPause);

    $(document).on('click', '.card .play-button, .quick-play-item, .track-item', function(e) {
        e.stopPropagation();
        const songpath = $(this).closest('[data-song]').data('song') || $(this).data('song');
        const songtitle = $(this).closest('[data-title]').data('title') || $(this).data('title');
        const songauthor = $(this).closest('[data-author]').data('author') || $(this).data('author');
        const songcover = $(this).closest('[data-cover]').data('cover') || $(this).data('cover');
        const songid = $(this).closest('[data-id]').data('id') || $(this).data('id');
        if (currentSong === songpath && isPlaying) {
            togglePlayPause();
        } else {
            playSong(songpath,songtitle,songauthor,songcover,songid);
        }
    });

    $(document).on('click', '.card', function(e) {
        if (!$(e.target).closest('.play-button').length) {
            const songpath = $(this).data('song');
            const songtitle = $(this).data('title');
            const songauthor = $(this).data('author');
            const songcover = $(this).data('cover');
            const songid = $(this).data('id');
            playSong(songpath,songtitle,songauthor,songcover,songid);
        }
    });

    progressBar.click(function(e) {
        if (audio.duration) {
            const rect = this.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audio.currentTime = percent * audio.duration;
        }
    });

    volumeBar.click(function(e) {
        const rect = this.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        audio.volume = percent;
        volumeFill.css('width', (percent * 100) + '%');
        const volumeIcon = volumeBtn.find('i');
        if (percent === 0) {
            volumeIcon.removeClass().addClass('fas fa-volume-mute');
        } else if (percent < 0.5) {
            volumeIcon.removeClass().addClass('fas fa-volume-down');
        } else {
            volumeIcon.removeClass().addClass('fas fa-volume-up');
        }
        localStorage.setItem('audioVolume', percent);
    });

    volumeBtn.click(function() {
        if (audio.volume > 0) {
            audio.volume = 0;
            volumeFill.css('width', '0%');
            $(this).find('i').removeClass().addClass('fas fa-volume-mute');
        } else {
            const savedVolume = parseFloat(localStorage.getItem('audioVolume')) || 0.7;
            audio.volume = savedVolume;
            volumeFill.css('width', (savedVolume * 100) + '%');
            $(this).find('i').removeClass().addClass('fas fa-volume-up');
        }
    });

    audio.addEventListener('timeupdate', updateProgress);
    
    audio.addEventListener('loadedmetadata', function() {
        totalTimeSpan.text(formatTime(audio.duration));
    });

    audio.addEventListener('ended', function() {
        if (repeat === true) {  
            audio.currentTime = 0; 
            audio.play();
        } else {
            isPlaying = false;
            playPauseIcon.removeClass('fa-pause').addClass('fa-play');
            updateAllPlayButtons();
            progressFill.css('width', '0%');
            currentTimeSpan.text('0:00');
        }
    });

    $('#shuffle-btn').click(function() {
        $(this).toggleClass('active');
    });

    $('#repeat-btn').click(function() {
        $(this).toggleClass('active');
        repeat = !repeat; 
    });

    $('.playlist-item').click(function() {
        $('.nav-item, .playlist-item').removeClass('active');
        $(this).addClass('active');
    });

    $('.nav-item').click(function(e) {
        $('.nav-item, .playlist-item').removeClass('active');
        $(this).addClass('active');
        
        if ($(this).find('i').hasClass('fa-home')) {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }
    });
    const savedVolume = parseFloat(localStorage.getItem('audioVolume')) || 0.7;
    audio.volume = savedVolume;
    volumeFill.css('width', (savedVolume * 100) + '%');

    $(document).keydown(function(e) {
        if (e.code === 'Space' && !$(e.target).is('input, textarea')) {
            e.preventDefault();
            togglePlayPause();
        }
    });



        $(document).on('click', '#logout', function() {
        $.ajax({
            url: 'ajax/logout.php',
            method: 'POST',
            success: function(response) {
                $('#logout').replaceWith(`
                    <div class="nav-item" id='login'>
                        <i class="fas fa-user"></i>
                        <span>Zaloguj</span>
                    </div>
                `);
                $('#nav-add').remove();
                $('#nav-likes').remove();
                $('#nav-admin').remove();
            },
            error: function() {
                console.error('Błąd podczas wylogowywania.');
            }
        });
    });

        $(document).on('click', '#login', function() {window.location.href = "https://romasolutions.pl/spotify/login";});

});