<?php 
session_start();
if(!isset($_SESSION['user_id']) && !isset($_SESSION['user_nickname'])) {
    header("Location: login.php");
    exit();
} 
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj piosenkę</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/upload.css">
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/1/19/Spotify_logo_without_text.svg">
</head>
<body>

 <div class="container">
        <div class="header">
            <h1><a href='https://romasolutions.pl/spotify/'><i class="fab fa-spotify"></i></a> Dodaj Piosenkę</h1>
            <p>Udostępnij swoją muzykę światu</p>
        </div>

        <div class="form-container">
            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="addSongForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Tytuł piosenki</label>
                    <input type="text" id="title" name="title" required placeholder="Wprowadź tytuł piosenki...">
                </div>

                <div class="file-upload-container">
                    <div class="file-upload" id="coverUpload">
                        <input type="file" id="coverFile" name="cover" accept="image/*" required>
                        <div class="file-upload-content">
                            <i class="fas fa-image"></i>
                            <h3>Okładka</h3>
                            <p>Przeciągnij lub kliknij aby wybrać</p>
                            <p>PNG, JPG (maks. 10MB)</p>
                        </div>
                        <div class="file-name" id="coverFileName"></div>
                        <div class="cover-preview" id="coverPreview"></div>
                    </div>

                    <div class="file-upload" id="audioUpload">
                        <input type="file" id="audioFile" name="audio" accept="audio/*" required>
                        <div class="file-upload-content">
                            <i class="fas fa-music"></i>
                            <h3>Plik audio</h3>
                            <p>Przeciągnij lub kliknij aby wybrać</p>
                            <p>MP3, WAV, FLAC (maks. 50MB)</p>
                        </div>
                        <div class="file-name" id="audioFileName"></div>
                        <div class="audio-preview" id="audioPreview"></div>
                    </div>
                </div>

                <div class="playlist-section">
                    <h3><i class="fas fa-list"></i> Dodaj do playlisty</h3>
                    <p>Opcjonalnie: wybierz playlistę, do której chcesz dodać tę piosenkę</p>
                    <div class="form-group">
                        <select id="playlist" name="playlist">
                            <option value="">Wybierz playlistę (opcjonalnie)</option>
                        </select>
                    </div>
                </div>

                <div class="submit-container">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <div class="loading-spinner" id="loadingSpinner"></div>
                        <span id="submitText">Dodaj Piosenkę</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
 $(document).ready(function() {
            loadPlaylists();

            $('#coverUpload').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $('#coverUpload').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $('#coverUpload').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#coverFile')[0].files = files;
                    handleCoverPreview(files[0]);
                }
            });

            $('#audioUpload').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $('#audioUpload').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $('#audioUpload').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#audioFile')[0].files = files;
                    handleAudioPreview(files[0]);
                }
            });

            $('#coverFile').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    handleCoverPreview(file);
                }
            });

            $('#audioFile').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    handleAudioPreview(file);
                }
            });

            $('#addSongForm').on('submit', function(e) {
                e.preventDefault();
                uploadSong();
            });

            function loadPlaylists() {
                $.ajax({
                    url: 'ajax/get_playlists.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const playlistSelect = $('#playlist');
                            playlistSelect.empty();
                            playlistSelect.append('<option value="">Wybierz playlistę (opcjonalnie)</option>');
                            
                            response.playlists.forEach(function(playlist) {
                                playlistSelect.append(`<option value="${playlist.id}">${playlist.name}</option>`);
                            });
                        }
                    },
                    error: function() {
                        console.error('Błąd podczas ładowania playlist');
                    }
                });
            }

            function handleCoverPreview(file) {
                if (!file.type.startsWith('image/')) {
                    showError('Proszę wybrać plik obrazu');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    showError('Plik okładki jest za duży (maks. 10MB)');
                    return;
                }

                $('#coverFileName').text(file.name);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $('<img>').attr('src', e.target.result);
                    $('#coverPreview').html(img);
                };
                reader.readAsDataURL(file);
            }

            function handleAudioPreview(file) {
                if (!file.type.startsWith('audio/')) {
                    showError('Proszę wybrać plik audio');
                    return;
                }

                if (file.size > 50 * 1024 * 1024) {
                    showError('Plik audio jest za duży (maks. 50MB)');
                    return;
                }

                $('#audioFileName').text(file.name);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const audio = $('<audio>').attr({
                        'src': e.target.result,
                        'controls': true
                    });
                    $('#audioPreview').html(audio);
                };
                reader.readAsDataURL(file);
            }

            function uploadSong() {
                const formData = new FormData();
                const title = $('#title').val().trim();
                const coverFile = $('#coverFile')[0].files[0];
                const audioFile = $('#audioFile')[0].files[0];
                const playlistId = $('#playlist').val();

                if (!title) {
                    showError('Proszę wprowadzić tytuł piosenki');
                    return;
                }

                if (!coverFile) {
                    showError('Proszę wybrać okładkę');
                    return;
                }

                if (!audioFile) {
                    showError('Proszę wybrać plik audio');
                    return;
                }

                formData.append('title', title);
                formData.append('cover', coverFile);
                formData.append('audio', audioFile);
                if (playlistId) {
                    formData.append('playlist_id', playlistId);
                }

                showLoading();

                $.ajax({
                    url: 'ajax/upload_song.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoading();
                        
                        if (response.success) {
                            showSuccess('Piosenka została dodana pomyślnie!');
                            resetForm();
                        } else {
                            showError(response.message || 'Wystąpił błąd podczas dodawania piosenki');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        console.error('Błąd AJAX:', error);
                        showError('Wystąpił błąd podczas przesyłania pliku');
                    }
                });
            }

            function showError(message) {
                $('#errorMessage').text(message).show();
                $('#successMessage').hide();
                $('html, body').animate({scrollTop: 0}, 300);
            }

            function showSuccess(message) {
                $('#successMessage').text(message).show();
                $('#errorMessage').hide();
                $('html, body').animate({scrollTop: 0}, 300);
            }

            function showLoading() {
                $('#loadingSpinner').show();
                $('#submitText').text('Dodawanie...');
                $('#submitBtn').prop('disabled', true);
            }

            function hideLoading() {
                $('#loadingSpinner').hide();
                $('#submitText').text('Dodaj Piosenkę');
                $('#submitBtn').prop('disabled', false);
            }

            function resetForm() {
                $('#addSongForm')[0].reset();
                $('#coverPreview').empty();
                $('#audioPreview').empty();
                $('#coverFileName').text('');
                $('#audioFileName').text('');
                $('#playlist').val('');
            }
        });

    </script>
</body>
</html>