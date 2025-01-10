import re
from PyPDF2 import PdfReader

def extract_ticket_route(pdf_path):
    try:
        with open(pdf_path, 'rb') as file:
            reader = PdfReader(file)
            text = ''.join(page.extract_text() for page in reader.pages)

        # Print extracted text for debugging
        #print("Extracted Text:")
        #print(text)

        # Regex for extracting the route
        route_match = re.search(r"Your round trip .*?\((\w+)\)\s*-\s*\(?[\s\S]*?(\w+)\)?.*?\((\w+)\)", text)
        print("Regex Match Debug:", route_match)  # Debugging regex match

        if route_match:
            departure_code = route_match.group(1)  # First airport code (e.g., DAC)
            destination_code = route_match.group(2)  # Second airport code (e.g., CAN)
            ticket_route = f"{departure_code}/{destination_code}/{departure_code}"
        else:
            ticket_route = "Route not found"

        return {"Ticket Route": ticket_route}

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    pdf_path = "uploads/B090YH.pdf"  # Replace with the correct path to your PDF
    extracted_route = extract_ticket_route(pdf_path)
    print(extracted_route)
