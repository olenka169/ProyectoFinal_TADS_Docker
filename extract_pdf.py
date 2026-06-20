import pdfplumber

with pdfplumber.open("TADS - AVANCE 05.pdf") as pdf:
    for i, page in enumerate(pdf.pages):
        print(f"--- Page {i+1} ---")
        print(page.extract_text())
