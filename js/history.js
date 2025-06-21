function updateHistory(currentSongId) {
    if (!currentSongId) {
        console.log('No song ID provided');
        return;
    }
    
    $.ajax({
        url: 'ajax/update_history.php',
        type: 'POST',
        data: {
            song_id: currentSongId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log('History updated successfully');
            } else {
                console.log('Failed to update history: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error updating history: ' + error);
        }
    });
}

function showHistory() {
    $('.box').hide();
    $('#history-list').show();
    $('#history-list').empty();
    
    $('#history-list').html('<div class="history-loader">Ładowanie historii...</div>');
    
    $.ajax({
        url: 'ajax/get_history.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#history-list').empty();
            
            if (response.success && response.data.length > 0) {
                response.data.forEach(function(song, index) {
                    var trackItem = $(
                        '<div class="track-item" data-song="songs/' + song.path + '" data-id="' + song.id + '" data-title="' + song.title + '" data-author="' + song.artist + '" data-cover="' + song.cover + '">' +
                            '<div class="track-number">' + (index + 1) + '</div>' +
                            '<div class="track-info">' +
                                '<div class="track-image" style="background-image: url(covers/' + song.cover + ')"></div>' +
                                '<div class="track-details">' +
                                    '<div class="track-title">' + song.title + '</div>' +
                                    '<div class="track-artist">' + song.artist + '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="track-time">' + formatHistoryTime(song.time) + '</div>' +
                        '</div>'
                    );
                    $('#history-list').append(trackItem);
                });
            } else {
                $('#history-list').html('<div class="no-history">Brak historii odsłuchań</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#history-list').html('<div class="history-error">Błąd podczas ładowania historii</div>');
            console.log('Error loading history: ' + error);
        }
    });
}

function formatHistoryTime(timeString) {
    const date = new Date(timeString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) {
        return 'Teraz';
    } else if (diffMins < 60) {
        return diffMins + ' min temu';
    } else if (diffHours < 24) {
        return diffHours + ' godz. temu';
    } else {
        return diffDays + ' dni temu';
    }
}

$(document).ready(function() {
    $('#nav-history').click(function() {
        showHistory();
    });
});