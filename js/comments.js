function songComments(currentSongId) {
    $('#song-comments').empty();
    
    const commentsHTML = `
        <div class="comments-header">
            <i class="fas fa-comments"></i> Komentarze
        </div>
        
        <div class="comment-form">
            <textarea class="comment-textarea" placeholder="Napisz komentarz do tej piosenki..." maxlength="1000"></textarea>
            <div class="comment-form-actions">
                <button class="btn-comment btn-comment-secondary" onclick="cancelComment()">Anuluj</button>
                <button class="btn-comment btn-comment-primary" onclick="submitComment(${currentSongId})" disabled>Dodaj komentarz</button>
            </div>
        </div>
        
        <div class="loading-spinner">
            <i class="fas fa-spinner"></i>
            <div>Ładowanie komentarzy...</div>
        </div>
        
        <div class="comments-list">
        </div>
    `;
    
    $('#song-comments').html(commentsHTML);
    
    $('.comment-textarea').on('input', function() {
        const hasContent = $(this).val().trim().length > 0;
        $('.btn-comment-primary').prop('disabled', !hasContent);
    });
    
    loadComments(currentSongId);
    
    window.currentSongId = currentSongId;
}

function loadComments(songId) {
    $('.loading-spinner').show();
    $('.comments-list').hide();
    
    $.ajax({
        url: 'ajax/get_comments.php',
        method: 'POST',
        data: { song_id: songId },
        dataType: 'json',
        success: function(response) {
            $('.loading-spinner').hide();
            $('.comments-list').show();
            
            if (response.success) {
                displayComments(response.comments);
            }
        },
        error: function(xhr, status, error) {
            $('.loading-spinner').hide();
            $('.comments-list').show();
            console.error('AJAX Error:', error);
        }
    });
}

function displayComments(comments) {
    const commentsList = $('.comments-list');
    
    if (comments.length === 0) {
        commentsList.html(`
            <div class="empty-comments">
                <i class="fas fa-comment-slash"></i>
                <h3>Brak komentarzy</h3>
                <p>Bądź pierwszy i dodaj komentarz do tej piosenki!</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        html += buildCommentHTML(comment);
    });
    
    commentsList.html(html);
}

function buildCommentHTML(comment) {
    const timeAgo = formatTimeAgo(comment.created_at);
    let repliesHTML = '';
    
    if (comment.replies && comment.replies.length > 0) {
        comment.replies.forEach(reply => {
            const replyTimeAgo = formatTimeAgo(reply.created_at);
            repliesHTML += `
                <div class="comment-reply">
                    <div class="comment-header">
                        <span class="comment-author">${escapeHtml(reply.username)}</span>
                        <span class="comment-time">${replyTimeAgo}</span>
                    </div>
                    <div class="comment-content">${escapeHtml(reply.content)}</div>
                </div>
            `;
        });
    }
    
    return `
        <div class="comment-item" data-comment-id="${comment.id}">
            <div class="comment-header">
                <span class="comment-author">${escapeHtml(comment.username)}</span>
                <span class="comment-time">${timeAgo}</span>
            </div>
            <div class="comment-content">${escapeHtml(comment.content)}</div>
            <div class="comment-actions">
                <button class="comment-action" onclick="toggleReplyForm(${comment.id})">
                    <i class="fas fa-reply"></i> Odpowiedz
                </button>
                ${comment.replies.length > 0 ? `<span class="comment-action"><i class="fas fa-comments"></i> ${comment.replies.length} ${comment.replies.length === 1 ? 'odpowiedź' : comment.replies.length < 5 ? 'odpowiedzi' : 'odpowiedzi'}</span>` : ''}
            </div>
            <div class="reply-form" id="reply-form-${comment.id}">
                <textarea class="reply-textarea" placeholder="Napisz odpowiedź..." maxlength="1000"></textarea>
                <div class="reply-form-actions">
                    <button class="btn-comment btn-comment-secondary" onclick="cancelReply(${comment.id})">Anuluj</button>
                    <button class="btn-comment btn-comment-primary" onclick="submitReply(${comment.id})" disabled>Odpowiedz</button>
                </div>
            </div>
            ${repliesHTML ? `<div class="comment-replies">${repliesHTML}</div>` : ''}
        </div>
    `;
}

function submitComment(songId) {
    const content = $('.comment-textarea').val().trim();
    
    if (!content) {
        return;
    }
    
    const submitBtn = $('.comment-form .btn-comment-primary');
    const originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Dodawanie...');
    
    $.ajax({
        url: 'ajax/add_comment.php',
        method: 'POST',
        data: {
            song_id: songId,
            content: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('.comment-textarea').val('');
                loadComments(songId);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        },
        complete: function() {
            submitBtn.prop('disabled', true).text(originalText);
        }
    });
}

function toggleReplyForm(commentId) {
    const replyForm = $(`#reply-form-${commentId}`);
    const isActive = replyForm.hasClass('active');
    
    $('.reply-form.active').removeClass('active');
    $('.reply-textarea').val('');
    
    if (!isActive) {
        replyForm.addClass('active');
        const textarea = replyForm.find('.reply-textarea');
        textarea.focus();
        
        textarea.off('input.reply').on('input.reply', function() {
            const hasContent = $(this).val().trim().length > 0;
            replyForm.find('.btn-comment-primary').prop('disabled', !hasContent);
        });
    }
}

function cancelReply(commentId) {
    const replyForm = $(`#reply-form-${commentId}`);
    replyForm.removeClass('active');
    replyForm.find('.reply-textarea').val('');
    replyForm.find('.btn-comment-primary').prop('disabled', true);
}

function submitReply(commentId) {
    const replyForm = $(`#reply-form-${commentId}`);
    const content = replyForm.find('.reply-textarea').val().trim();
    
    if (!content) {
        return;
    }
    
    const songId = window.currentSongId;
    const submitBtn = replyForm.find('.btn-comment-primary');
    const originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Dodawanie...');
    
    $.ajax({
        url: 'ajax/add_comment.php',
        method: 'POST',
        data: {
            song_id: songId,
            parent_id: commentId,
            content: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cancelReply(commentId);
                loadComments(songId);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

function cancelComment() {
    $('.comment-textarea').val('');
    $('.comment-form .btn-comment-primary').prop('disabled', true);
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'przed chwilą';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min temu`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} godz. temu`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} dni temu`;
    
    return date.toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function initSongComments() {
    $(document).on('click', '[data-songid]', function() {
        const currentSongId = $(this).attr('data-songid');
        if (currentSongId) {
            songComments(parseInt(currentSongId));
        }
    });
}

$(document).ready(function() {
    initSongComments();
});