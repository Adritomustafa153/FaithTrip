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
            "CZ": "China Southern Airlines(CZ)",
            "AA": "American Airlines(AA)",
            "DL": "Delta Airlines(DL)",
            "BA": "British Airways(BA)",
            "EK": "Emirates(EK)",
            "QR": "Qatar Airways(QR)",
            "TK": "Turkish Airlines(TK)",
            "BG": "Biman Bangladesh Airlines(BG)",
            "BS": "US Bangla Airlines(BS)",
            "VQ": "Novoair(VQ)",
            "2A": "Air Astra(2A)",
            "6E": "Indigo Air(6E)",
            "H9": "Himalaya Airlines(H9)",
            "J9": "Jazeera Airways(J9)",
            "G9": "Air Arabia(G9)",
            "IB": "Iberia Airlines(IB)",
            "9W": "Aeromexico(9W)",
            "9R": "Ryanair(9R)",
            "9K": "Kenya Airways(9K)",
            "9J": "Japan Airlines(9J)",
            "KU": "Kuwait Airways(KW)",
            "TG": "Thai Airways(TG)",
            "ET": " Ethiopian Airlines(ET)",
            "AI": "Air India(AI)",
            "SV": "Saudi Arabian Airlines(SV)",
            "SQ": "Singapore Airlines(SQ)",
            "UL": "Srilankan Airlines(UL)",
            "FZ": "Fly Dubai(FZ)",
            "MH": "Malaysian Airlines(MH)",
            "MU": "China Eastern Airlines(MU)",
            "GF": "Gulf Air(GF)",
            "WY": "Oman Air(WY)",
            "OD": "Malindo Air(OD)",
            "CX": "Cathay Pacific Airways(CX)",
            "UK": "Vistara(UK)",



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
        return_date_match = re.findall(r"\((\w+)\)[\s\S]*?\d{2}:\d{2}\s+\w{3},\s+(\d{1,2}\s+\w+\s+\d{4})", text)
        return_date = return_date_match[1][1] if len(return_date_match) > 1 else "Return date not found"



        # Extract Ticket Route
        route_match = re.search(r"Your round trip .*?\((\w+)\)\s*-\s*.*?\(-?\s*(\w+)\)", text)
        if route_match:
            departure_code = route_match.group(1)  # First airport code (e.g., DAC)
            destination_code = route_match.group(2)  # Second airport code (e.g., CAN)
            ticket_route = f"{departure_code}/{destination_code}/{departure_code}"
        else:
            ticket_route = "Route not found"

        data = {
            "PNR": re.search(r"PNR:\s+\w+-(\w+)", text).group(1) if re.search(r"PNR:\s+\w+-(\w+)", text) else "",
            "Passenger Name": re.search(r"Travelers\s*([\w\s]+)", text).group(1).replace("Tickets Number", "").strip() if re.search(r"Travelers\s*([\w\s]+)", text) else "",
            "Airline Name": airline_name,
            "Departure Date": departure_date,
            "Return Date": return_date,
            "Ticket Issue Date": re.search(r"Issued Date:\s+(\d{2}/\d{2}/\d{4})", text).group(1) if re.search(r"Issued Date:\s+(\d{2}/\d{2}/\d{4})", text) else "",
            "Ticket Number": re.search(r"Tickets Number\s*\n\s*[^\n]+\s+(\d+)", text).group(1) if re.search(r"Tickets Number\s*\n\s*[^\n]+\s+(\d+)", text) else "",
            "Ticket Route": ticket_route,

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
    print(json.dumps(extracted_data, indent=4))
    