import sys
import re
import json
from PyPDF2 import PdfReader

def extract_ticket_data(pdf_path):
    try:
        with open(pdf_path, 'rb') as file:
            reader = PdfReader(file)
            text = ''.join(page.extract_text() for page in reader.pages)

        # Split text into lines
        lines = text.splitlines()

        # Initialize variables
        departure_date = "Departure date not found"
        return_date = "Return date not found"

        # Loop through lines to find the departure date
        for i, line in enumerate(lines):
            # Look for a date in the format "HH:MM Day, DD Month YYYY" after a time
            departure_match = re.search(r"\d{2}:\d{2}\s+\w{3},\s+(\d{1,2}\s+\w+\s+\d{4})", line)
            if departure_match:
                departure_date = departure_match.group(1)  # Capture departure date

                # Look for the return date 5 lines below
                if i + 5 < len(lines):  # Ensure we don't go out of bounds
                    return_line = lines[i + 5]
                    return_match = re.search(r"\d{2}:\d{2}\s+\w{3},\s+(\d{1,2}\s+\w+\s+\d{4})", return_line)
                    if return_match:
                        return_date = return_match.group(1)  # Capture return date
                break  # Exit loop once departure date is found

        # Extract Ticket Route
        route_match = re.search(r"Your round trip .*?\((\w+)\)\s*-\s*.*?\((\w+)\)", text)
        if route_match:
            departure_code = route_match.group(1)  # First airport code
            destination_code = route_match.group(2)  # Second airport code
            ticket_route = f"{departure_code}/{destination_code}/{departure_code}"
        else:
            ticket_route = "Route not found"

        # Extract Ticket Issue Date
        ticket_issue_date = re.search(r"Issued Date:\s+(\d{2}/\d{2}/\d{4})", text)
        ticket_issue_date = ticket_issue_date.group(1) if ticket_issue_date else "Issue date not found"

        # Extract Ticket Number
        ticket_number_match = re.search(r"Tickets Number\s*\n\s*[^\n]+\s+(\d+)", text)
        ticket_number = ticket_number_match.group(1) if ticket_number_match else "Ticket number not found"

        data = {
            "PNR": re.search(r"PNR:\s+\w+-(\w+)", text).group(1) if re.search(r"PNR:\s+\w+-(\w+)", text) else "",
            "Passenger Name": re.search(r"Travelers\s*([\w\s]+)", text).group(1).replace("Tickets Number", "").strip() if re.search(r"Travelers\s*([\w\s]+)", text) else "",
            "Airline Name": "Unknown Airline",  # Replace with dynamic extraction if needed
            "Departure Date": departure_date,
            "Return Date": return_date,
            "Ticket Issue Date": ticket_issue_date,
            "Ticket Number": ticket_number,
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
