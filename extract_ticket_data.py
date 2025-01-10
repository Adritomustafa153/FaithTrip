import sys
import re
import json
from PyPDF2 import PdfReader

def extract_ticket_data(pdf_path):
    try:

        

        with open(pdf_path, 'rb') as file:
            reader = PdfReader(file)
            text = ''.join(page.extract_text() for page in reader.pages)
#For catches the airlines name.
            lines = text.splitlines()

        # Extract Passenger Name
        passenger_match = re.search(r"Travelers\s*([\w\s]+)", text)
        passenger_name = (
            passenger_match.group(1).replace("Tickets Number", "").strip()
            if passenger_match
            else ""
        )

         # Dictionary of airline codes and names
        airline_codes = {
            "CZ": "China Southern Airlines",
            "AA": "American Airlines",
            "DL": "Delta Airlines",
            "BA": "British Airways",
            "EK": "Emirates",
            "QR": "Qatar Airways",
            "TK": "Turkish Airlines",
            "BG": "Biman Bangladesh Airlines",
            "BS": "US Bangla Airlines",
            "VQ": "Novoair",
            "2A": "Air Astra",
            "6E": "Indigo Air",
            "H9": "Himalaya Airlines",
            "J9": "Jazeera Airways",
            "G9": "Air Arabia",
            "IB": "Iberia Airlines",
            "9W": "Aeromexico",
            "9R": "Ryanair",
            "9K": "Kenya Airways",
            "9J": "Japan Airlines",
            "KU": "Kuwait Airways",
            "TG": "Thai Airways",
            "ET": " Ethiopian Airlines",
            "AI": "Air India",
            "SV": "Saudi Arabian Airlines",
            "SQ": "Singapore Airlines",
            "UL": "Srilankan Airlines",
            "FZ": "Fly Dubai",
            "MH": "Malaysian Airlines",
            "MU": "China Eastern Airlines",
            "GF": "Gulf Air",
            "WY": "Oman Air",
            "OD": "Malindo Air",
            "CX": "Cathay Pacific Airways",


            # Add more airline codes and names as needed
        }

        with open(pdf_path, 'rb') as file:
            reader = PdfReader(file)
            text = ''.join(page.extract_text() for page in reader.pages)

        # Extract PNR and Airline Code
        pnr_match = re.search(r"PNR:\s+(\w+)-(\w+)", text)
        airline_code = pnr_match.group(1) if pnr_match else ""
        pnr = pnr_match.group(2) if pnr_match else ""

        # Find Airline Name from Airline Code
        airline_name = airline_codes.get(airline_code, "Unknown Airline")

        # Extract Passenger Name
        passenger_match = re.search(r"Travelers\s*([\w\s]+)", text)
        passenger_name = (
            passenger_match.group(1).replace("Tickets Number", "").strip()
            if passenger_match
            else ""
        )
        # Extract Departure Date
        departure_date_match = re.search(r"Dhaka\s*\(DAC\)[\s\S]*?\d{2}:\d{2}\s+(.*?),\s+(\d{1,2}\s+\w+\s+\d{4})", text)
        departure_date = departure_date_match.group(2) if departure_date_match else ""

        # Extract Return Date
        return_date_match = re.search(r"Guangzhou\s*\(CAN\)[\s\S]*?\d{2}:\d{2}\s+Fri,\s+(\d{1,2}\s+\w+\s+\d{4})", text)
        return_date = return_date_match.group(1) if return_date_match else ""

        data = {
            "PNR": re.search(r"PNR:\s+\w+-(\w+)", text).group(1) if re.search(r"PNR:\s+\w+-(\w+)", text) else "",
            "Passenger Name": re.search(r"Travelers\s*([\w\s]+)", text).group(1).replace("Tickets Number", "").strip() if re.search(r"Travelers\s*([\w\s]+)", text) else "",
            "Airline Name": airline_name,
            "Departure Date": departure_date,
            "Return Date": return_date,
            "Ticket Issue Date": re.search(r"Issued Date:\s+(\d{2}/\d{2}/\d{4})", text).group(1) if re.search(r"Issued Date:\s+(\d{2}/\d{2}/\d{4})", text) else "",
            "Ticket Number": re.search(r"Tickets Number\s*\n\s*[^\n]+\s+(\d+)", text).group(1) if re.search(r"Tickets Number\s*\n\s*[^\n]+\s+(\d+)", text) else "",
            
        }

        return data
        
    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({"error": "Usage: python extract_ticket_data.py <pdf_path>"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    extracted_data = extract_ticket_data(pdf_path)
    print(json.dumps(extracted_data))
    