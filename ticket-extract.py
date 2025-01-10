# Required Libraries
import os
import re
import smtplib
from email.message import EmailMessage
from PyPDF2 import PdfReader
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer # type: ignore
from reportlab.lib.styles import getSampleStyleSheet # type: ignore
from flask import Flask, request, render_template, redirect, url_for # type: ignore
import pymysql # type: ignore

# Flask Setup
app = Flask(__name__)
UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Database Connection
def db_connection():
    return pymysql.connect(host="localhost", user="root", password="", database="faithtrip")

# Helper Function: Extract Ticket Data
def extract_ticket_data(pdf_path):
    with open(pdf_path, 'rb') as file:
        reader = PdfReader(file)
        text = ''.join(page.extract_text() for page in reader.pages)

    data = {
        "PNR": re.search(r"PNR:\s+(\w+)", text).group(1),
        "Passenger Name": re.search(r"Passenger:\s+(.+)", text).group(1),
        "Airline Name": re.search(r"Airline:\s+(.+)", text).group(1),
        "Departure Date": re.search(r"Departure:\s+(\d{2}/\d{2}/\d{4})", text).group(1),
        "Return Date": re.search(r"Return:\s+(\d{2}/\d{2}/\d{4})", text).group(1),
        "Ticket Issue Date": re.search(r"Issue Date:\s+(\d{2}/\d{2}/\d{4})", text).group(1),
        "Ticket Number": re.search(r"Ticket No:\s+(\d+)", text).group(1),
    }
    return data

# Helper Function: Generate Invoice
def generate_invoice(data, output_file):
    doc = SimpleDocTemplate(output_file)
    styles = getSampleStyleSheet()
    story = [Paragraph("Invoice", styles["Title"])]

    for key, value in data.items():
        story.append(Paragraph(f"{key}: {value}", styles["Normal"]))
        story.append(Spacer(1, 12))

    doc.build(story)

# Helper Function: Send Email
def send_email(receiver_email, subject, body, attachment_path):
    msg = EmailMessage()
    msg["From"] = "director@faithtrip.net"
    msg["To"] = "adrito642@gmail.com"
    msg["Subject"] = "Air Ticket Invoice"
    msg.set_content(body)

    with open(attachment_path, "rb") as f:
        msg.add_attachment(f.read(), maintype="application", subtype="pdf", filename=os.path.basename(attachment_path))

    with smtplib.SMTP("smtp.gmail.com", 587) as smtp:
        smtp.starttls()
        smtp.login("director@faithtrip.net", "F@!th24Trip")
        smtp.send_message(msg)

# Routes
@app.route('/')
def home():
    conn = db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT id, name FROM passengers")
    passengers = cursor.fetchall()
    conn.close()
    return render_template('index.html', passengers=passengers)

@app.route('/upload', methods=['POST'])
def upload():
    pdf_file = request.files['ticket-file']
    ticket_price = request.form['ticket-price']
    passenger_id = request.form['passenger']

    if pdf_file:
        pdf_path = os.path.join(app.config['UPLOAD_FOLDER'], pdf_file.filename)
        pdf_file.save(pdf_path)
        data = extract_ticket_data(pdf_path)
        data["Ticket Price"] = ticket_price
        
        conn = db_connection()
        cursor = conn.cursor()
        cursor.execute(
            "INSERT INTO tickets (pnr, passenger_name, airline_name, departure_date, return_date, issue_date, ticket_number, price, passenger_id) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
            (data["PNR"], data["Passenger Name"], data["Airline Name"], data["Departure Date"], data["Return Date"], data["Ticket Issue Date"], data["Ticket Number"], ticket_price, passenger_id)
        )
        conn.commit()
        conn.close()

        return redirect(url_for('generate_invoice', ticket_id=cursor.lastrowid))
    return redirect(url_for('home'))

@app.route('/generate-invoice/<int:ticket_id>')
def generate_invoice_page(ticket_id):
    conn = db_connection()
    cursor = conn.cursor(pymysql.cursors.DictCursor)
    cursor.execute("SELECT * FROM tickets WHERE id = %s", (ticket_id,))
    ticket = cursor.fetchone()
    conn.close()

    if ticket:
        invoice_path = os.path.join('invoices', f"invoice_{ticket['pnr']}.pdf")
        os.makedirs('invoices', exist_ok=True)
        generate_invoice(ticket, invoice_path)
        return f"Invoice generated: <a href='/{invoice_path}'>{invoice_path}</a>"

    return "Ticket not found."

@app.route('/send-email/<int:ticket_id>', methods=['POST'])
def send_email_route(ticket_id):
    conn = db_connection()
    cursor = conn.cursor(pymysql.cursors.DictCursor)
    cursor.execute("SELECT * FROM tickets WHERE id = %s", (ticket_id,))
    ticket = cursor.fetchone()
    conn.close()

    if ticket:
        invoice_path = os.path.join('invoices', f"invoice_{ticket['pnr']}.pdf")
        receiver_email = request.form['email']
        send_email(receiver_email, "Your Invoice", "Please find your invoice attached.", invoice_path)
        return "Email sent successfully."

    return "Ticket not found."

# Run Application
if __name__ == '__main__':
    app.run(debug=True)
