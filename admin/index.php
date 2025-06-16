<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/1/19/Spotify_logo_without_text.svg">
</head>
<body>
    <div class="container">
         <div class="sidebar">
            <a href='https://romasolutions.pl/spotify/' style='text-decoration:none;'><div class="logo">
                <i class="fab fa-spotify"></i> <div id='logo-txt'>Spotify</div>
            </div></a>
            
            <nav>   
               <li class="nav-item">
                    <div class="nav-link active" data-tab="dashboard">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </div>
                </li>
                <li class="nav-item">
                    <div class="nav-link" data-tab="songs">
                        <i class="fas fa-gear"></i>
                        <span>Piosenki</span>
                    </div>
                </li>
                <li class="nav-item">
                    <div class="nav-link" data-tab="logs">
                        <i class="fas fa-clipboard"></i>
                        <span>Logi</span>
                    </div>
                </li>

                
            </nav>
        </div>

        <div class="main-content">

            <div id="alerts"></div>

            <div id="dashboard" class="tab-content active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Zarejestrowani użytkownicy</h3>
                        <div class="value" id="users-count">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Liczba piosenek</h3>
                        <div class="value" id="songs-count">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Liczba playlist</h3>
                        <div class="value" id="playlists-count">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Aktywność dzisiaj</h3>
                        <div class="value" id="today-activity">-</div>
                    </div>
                </div>

                <div class="section">
                    <h2>Ostatnie działania</h2>
                    <div class="logs-container" id="recent-logs">
                        <div class="loading">Ładowanie...</div>
                    </div>
                </div>
            </div>

            <div id="songs" class="tab-content">
                <div class="section">
                    <h2>Zarządzanie piosenkami</h2>
                    <div class="search-box">
                        <input type="text" id="songs-search" placeholder="Szukaj piosenek...">
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tytuł</th>
                                    <th>Użytkownik</th>
                                    <th>Okładka</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody id="songs-table">
                                <tr>
                                    <td colspan="5" class="loading">Ładowanie piosenek...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="logs" class="tab-content">
                <div class="section">
                    <h2>Logi administracyjne</h2>
                    <div class="logs-container" id="admin-logs">
                        <div class="loading">Ładowanie logów...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="editSongModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edytuj piosenkę</h3>
            <form id="editSongForm">
                <input type="hidden" id="edit-song-id">
                <div class="form-group">
                    <label for="edit-song-title">Tytuł piosenki:</label>
                    <input type="text" id="edit-song-title" required>
                </div>
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('editSongModal')">Anuluj</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.nav-link').click(function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                switchTab(tab);
            });

            loadDashboardData();
            loadRecentLogs();

            $('#songs-search').on('input', function() {
                const searchTerm = $(this).val();
                loadSongs(searchTerm);
            });

            $('#editSongForm').submit(function(e) {
                e.preventDefault();
                updateSong();
            });

            $('.close').click(function() {
                $(this).closest('.modal').hide();
            });
        });

        function switchTab(tabName) {
            $('.nav-link').removeClass('active');
            $(`.nav-link[data-tab="${tabName}"]`).addClass('active');
            
            $('.tab-content').removeClass('active');
            $(`#${tabName}`).addClass('active');

            switch(tabName) {
                case 'songs':
                    loadSongs();
                    break;
                case 'logs':
                    loadAdminLogs();
                    break;
                case 'dashboard':
                    loadDashboardData();
                    loadRecentLogs();
                    break;
            }
        }

        function loadDashboardData() {
            $.ajax({
                url: 'ajax/get_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#users-count').text(response.data.users_count);
                        $('#songs-count').text(response.data.songs_count);
                        $('#playlists-count').text(response.data.playlists_count);
                        $('#today-activity').text(response.data.today_activity);
                    }
                },
                error: function() {
                    showAlert('Błąd podczas ładowania statystyk', 'error');
                }
            });
        }


        
        function loadSongs(search = '') {
            $.ajax({
                url: 'ajax/get_songs.php',
                type: 'GET',
                data: { search: search },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        let html = '';
                        response.data.forEach(function(song) {
                            html += `
                                <tr>
                                    <td>${song.id}</td>
                                    <td>${song.title}</td>
                                    <td>${song.nickname || 'Nieznany'}</td>
                                    <td>${song.cover ? '<div class="player-image" style="background-image: url(https://romasolutions.pl/spotify/covers/' + song.cover + ');"></div>' : 'Brak'}</td>
                                    <td>
                                        <button class="btn btn-warning" onclick="editSong(${song.id}, '${song.title}')">Edytuj</button>
                                        <button class="btn btn-danger" onclick="deleteSong(${song.id})">Usuń</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#songs-table').html(html || '<tr><td colspan="5">Brak piosenek</td></tr>');
                    }
                },
                error: function() {
                    $('#songs-table').html('<tr><td colspan="5">Błąd podczas ładowania</td></tr>');
                }
            });
        }

        function loadUsers(search = '') {
            $.ajax({
                url: 'ajax/get_users.php',
                type: 'GET',
                data: { search: search },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        let html = '';
                        response.data.forEach(function(user) {
                            const isAdmin = user.is_admin ? 'Admin' : 'Użytkownik';
                            html += `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.email}</td>
                                    <td>${user.nickname}</td>
                                    <td>${isAdmin}</td>
                                    <td>
                                        <button class="btn btn-warning" onclick="toggleAdmin(${user.id})">${user.is_admin ? 'Usuń admin' : 'Nadaj admin'}</button>
                                        <button class="btn btn-danger" onclick="deleteUser(${user.id})">Usuń</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#users-table').html(html || '<tr><td colspan="5">Brak użytkowników</td></tr>');
                    }
                },
                error: function() {
                    $('#users-table').html('<tr><td colspan="5">Błąd podczas ładowania</td></tr>');
                }
            });
        }

        function loadRecentLogs() {
            $.ajax({
                url: 'ajax/get_logs.php',
                type: 'GET',
                data: { limit: 5 },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        let html = '';
                        response.data.forEach(function(log) {
                            html += `
                                <div class="log-entry">
                                    <div class="log-content">${log.content}</div>
                                </div>
                            `;
                        });
                        $('#recent-logs').html(html || '<div class="log-entry">Brak aktywności</div>');
                    }
                },
                error: function() {
                    $('#recent-logs').html('<div class="log-entry">Błąd podczas ładowania</div>');
                }
            });
        }

        function loadAdminLogs() {
            $.ajax({
                url: 'ajax/get_logs.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        let html = '';
                        response.data.forEach(function(log) {
                            html += `
                                <div class="log-entry">
                                    <div class="log-content">${log.content}</div>
                                </div>
                            `;
                        });
                        $('#admin-logs').html(html || '<div class="log-entry">Brak logów</div>');
                    }
                },
                error: function() {
                    $('#admin-logs').html('<div class="log-entry">Błąd podczas ładowania</div>');
                }
            });
        }

        function editSong(id, title) {
            $('#edit-song-id').val(id);
            $('#edit-song-title').val(title);
            $('#editSongModal').show();
        }

        function updateSong() {
            const id = $('#edit-song-id').val();
            const title = $('#edit-song-title').val();

            $.ajax({
                url: 'ajax/update_song.php',
                type: 'POST',
                data: {
                    id: id,
                    title: title
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showAlert('Piosenka została zaktualizowana', 'success');
                        $('#editSongModal').hide();
                        loadSongs();
                    } else {
                        showAlert(response.message || 'Błąd podczas aktualizacji', 'error');
                    }
                },
                error: function() {
                    showAlert('Błąd podczas aktualizacji piosenki', 'error');
                }
            });
        }

        function deleteSong(id) {
            if(confirm('Czy na pewno chcesz usunąć tę piosenkę?')) {
                $.ajax({
                    url: 'ajax/delete_song.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            showAlert('Piosenka została usunięta', 'success');
                            loadSongs();
                            loadDashboardData();
                        } else {
                            showAlert(response.message || 'Błąd podczas usuwania', 'error');
                        }
                    },
                    error: function() {
                        showAlert('Błąd podczas usuwania piosenki', 'error');
                    }
                });
            }
        }

        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
            $('#alerts').html(alertHtml);
            setTimeout(() => {
                $('#alerts').html('');
            }, 5000);
        }

        function closeModal(modalId) {
            $('#' + modalId).hide();
        }

    </script>
</body>
</html>