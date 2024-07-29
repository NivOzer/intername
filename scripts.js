//check submitted lead
function closeModal() {
    document.getElementById('messageModal').classList.remove('active');
}
window.onload = function() {
    var messageTextContent = document.getElementById('messageTextContent').value;
    if (messageTextContent) {
        var modal = document.getElementById('messageModal');
        var messageText = document.getElementById('messageText');
        messageText.textContent = messageTextContent;
        modal.classList.add('active');
    }
}
