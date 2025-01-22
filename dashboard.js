// Get the ticket_id from the data attribute
const ticketDataElement = document.getElementById('ticket-data');
const ticketId = ticketDataElement.getAttribute('data-ticket-id');

// Button functionalities
document.getElementById('generate-invoice').addEventListener('click', () => {
    window.location.href = `generate_invoice.php?ticket_id=${ticketId}`;
});

document.getElementById('send-to-spreadsheet').addEventListener('click', () => {
    fetch(`send_to_spreadsheet.php?ticket_id=${ticketId}`)
        .then(response => response.text())
        .then(data => alert(data))
        .catch(error => console.error('Error:', error));
});

document.getElementById('send-email').addEventListener('click', () => {
    fetch(`send_email.php?ticket_id=${ticketId}`)
        .then(response => response.text())
        .then(data => alert(data))
        .catch(error => console.error('Error:', error));
});
