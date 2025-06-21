<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/history.css">
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/1/19/Spotify_logo_without_text.svg">
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="logo">
                <i class="fab fa-spotify"></i> <div id='logo-txt'>Spotify</div>
            </div>
            
            <nav>
                <div id="nav-home" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Strona główna</span>
                </div>
                <div id="nav-search" class="nav-item">
                    <i class="fas fa-search"></i>
                    <span>Szukaj</span>
                </div>

                 
                <?php 
                session_start();
                if(isset($_SESSION['user_id']) && isset($_SESSION['user_nickname'])) {
                    echo "
                    <a href='https://romasolutions.pl/spotify/upload' class='nav-item' id='nav-add'>
                        <i class='fas fa-plus'></i>
                        <span>Dodaj piosenkę</span>
                    </a>

                    <div id='nav-likes' class='nav-item'>
                        <i class='fas fa-heart'></i>
                        <span>Polubione</span>
                    </div> 
                    
                    <div id='nav-history' class='nav-item'>
                        <i class='fas fa-clock'></i>
                        <span>Historia</span>
                    </div> 
                    
                    <div class='nav-item' id='logout'>
                        <i class='fas fa-right-from-bracket'></i>
                        <span>Wyloguj</span>
                    </div>";
                    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'){
                        echo "
                        <a href='https://romasolutions.pl/spotify/admin' class='nav-item' id='nav-admin'>
                            <i class='fas fa-gear'></i>
                            <span>Panel administratora</span>
                        </a>
                        ";
                    }
                } else {
                    echo "<div class='nav-item' id='login'>
                        <i class='fas fa-user'></i>
                        <span>Zaloguj</span>
                    </div>";
                }
                ?>

                
            </nav>
        </div>

     
        <div class='box' id="main-content">
            <div class="section" id="main-section">
                <h2 class="section-title">Polecane</h2>
                <div class="cards-grid" id="recently-played">   
                </div>
            </div>
        </div>

        <div class='box' id="search-section">
            <h2 style="margin-bottom: 20px; font-size: 24px;">Wyszukaj muzykę</h2>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Czego chcesz posłuchać?" id="searchInput">
                <div id="searchresults">
                </div>
            </div>
        </div>

        <div class='box' id="playlist-view">
            <div class="playlist-header">
                <div class="playlist-cover"></div>
                <div class="playlist-info">
                    <p class="playlist-meta">PLAYLISTA</p>
                    <h1></h1>
                </div>
            </div>

            
            <div class="track-list">
            </div>
        </div>

        <div class="song-view box" id="songView">
            <div class="song-view-content">
                <div id="songContent" style="display: none;">
                    <div class="song-main-info">
                        <div class="song-cover-large" id="songCoverLarge"></div>
                        <div class="song-details">
                            <div class="song-type">UTWÓR</div>
                            <h1 class="song-title-large" id="songTitleLarge">Tytuł piosenki</h1>
                            <div class="song-artist-large" id="songArtistLarge">Artysta</div>
                        </div>
                    </div>

                    <?php 
                        if(isset($_SESSION['user_id']) && isset($_SESSION['user_nickname'])) {
                            echo '
                            <div class="song-actions">
                                <button class="like-button" id="likeButton">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            <div id="song-comments"></div>
                            ';
                        }
                    ?>                    

                    
                </div>
            </div>
        </div>

        <div class='box' id="likes-list"></div>
        <div class='box' id="history-list"></div>
        
    </div>


    <div class="player">
        <div class="player-left">
            <div class="player-image" id="player-image"></div>
            <div class="player-info">
                <div class="player-title" id="player-title">Wybierz utwór</div>
                <div class="player-artist" id="player-artist">Artysta</div>
            </div>
        </div>

        <div class="player-controls">
            <div class="control-buttons">
                <button class="control-btn" id="shuffle-btn">
                    <i class="fas fa-random"></i>
                </button>
                <button class="control-btn" id="prev-btn">
                    <i class="fas fa-step-backward"></i>
                </button>
                <button class="control-btn play-pause-btn" id="play-pause-btn">
                    <i class="fas fa-play"></i>
                </button>
                <button class="control-btn" id="next-btn">
                    <i class="fas fa-step-forward"></i>
                </button>
                <button class="control-btn" id="repeat-btn">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
            <div class="progress-container">
                <span class="time" id="current-time">0:00</span>
                <div class="progress-bar" id="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>
                <span class="time" id="total-time">0:00</span>
            </div>
        </div>

        <div class="player-right">
            <div class="volume-container">
                <button class="control-btn" id="volume-btn">
                    <i class="fas fa-volume-up"></i>
                </button>
                <div class="volume-bar" id="volume-bar">
                    <div class="volume-fill" id="volume-fill"></div>
                </div>
            </div>
        </div>
    </div>

    <audio id="audio-player" preload="auto">
        <source src="" type="audio/mpeg">
        Twoja przeglądarka nie obsługuje elementu audio.
    </audio>
    <script src='js/feed.js'></script>
    <script src='js/main.js'></script>
    <script src='js/search.js'></script>
    <script src='js/playlist.js'></script>
    <script src='js/like.js'></script>
    <script src='js/comments.js'></script>
     <script src='js/history.js'></script>
</body>
</html>