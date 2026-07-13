---
name: UAM Excel Template Extraction
description: Instructions for parsing and extracting metadata from the standard User Access Matrix (UAM) Excel template.
---

# UAM Excel Template Structure

When building or modifying extraction logic for the User Access Matrix (UAM) Excel template, always assume the following strict structure unless specified otherwise:

## Top Metadata (Primary Source of Truth)
The top of the Excel template contains strictly defined coordinates. You MUST prioritize these specific cells over any dynamic label searching:
- **Application**: Cell B3 (Row 3, Column B).
- **Modul**: Cell B4 (Row 4, Column B). (e.g., "PS")
- **Access Owner (AO)**: Cell B5 (Row 5, Column B). (e.g., "SM Backbone Cloud & DEFA Planning")

## Signature Block (Bottom of File)
- **Requested By (NIK)**: The requester's NIK (a 5-8 digit number like "720203") is located at the very bottom of the document in the signature block. 
- Do not just extract the requester's name (e.g., "MOCHAMMAD HASAN JAUHARI"). You must extract the numeric NIK. 
- If the NIK is not explicitly labeled, search the cells immediately below the requester's name for a 5-8 digit number. 

## Extraction Principles
1. Always implement coordinate-based extraction (B3, B4, B5) first.
2. Only fall back to a dynamic keyword search if the primary coordinates are completely empty.
3. When searching for the requester's NIK, do a bottom-up scan of the last 50 rows rather than scanning from the top, because the signature block is at the end of the file.
