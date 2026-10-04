import csv
import json
import re

raw_csv_path = r"c:\Users\Serkan\.gemini\antigravity\scratch\findewerkstatt-de-tema\data\raw_input.csv"
output_json_path = r"c:\Users\Serkan\.gemini\antigravity\scratch\findewerkstatt-de-tema\data\workshops.json"

# Read raw CSV with UTF-8
workshops = []
with open(raw_csv_path, mode='r', encoding='utf-8', errors='replace') as f:
    reader = csv.DictReader(f)
    for row in reader:
        name = row.get('İşletme İsmi', '').strip()
        if not name or name == 'N/A' or name == 'A':
            # Skip invalid placeholder rows
            continue
        
        search_cat = row.get('Arama Kategorisi', '').strip()
        city_state = row.get('Şehir / Eyalet', '').strip()
        district = row.get('İlçe / Bölge', '').strip()
        plz = row.get('Posta Kodu (PLZ)', '').strip()
        if plz == 'N/A':
            plz = ''
        category = row.get('Kategori', '').strip()
        rating_str = row.get('Puan', '').strip().replace(',', '.')
        try:
            rating = float(rating_str) if rating_str and rating_str != 'N/A' else None
        except ValueError:
            rating = None
            
        reviews_str = row.get('Değerlendirme Sayısı', '').strip()
        # e.g. "179 Berichte", "1.500 Berichte", "33 Rezensionen"
        rev_count = 0
        m = re.search(r'([\d.]+)', reviews_str)
        if m:
            clean_num = m.group(1).replace('.', '')
            try:
                rev_count = int(clean_num)
            except ValueError:
                rev_count = 0
                
        phone = row.get('Telefon', '').strip()
        if phone == 'N/A':
            phone = ''
        address = row.get('Adres', '').strip()
        if address == 'N/A':
            address = ''
        website = row.get('Web Sitesi', '').strip()
        if website == 'N/A':
            website = ''
        photo_url = row.get('Görsel URL', '').strip()
        if photo_url == 'N/A':
            photo_url = ''
        maps_url = row.get('Google Maps URL', '').strip()
        if maps_url == 'N/A':
            maps_url = ''
            
        # Extract lat/lng from maps_url if present
        lat, lng = None, None
        coord_m = re.search(r'!8m2!3d([0-9.]+)!4d([0-9.]+)', maps_url)
        if coord_m:
            try:
                lat = float(coord_m.group(1))
                lng = float(coord_m.group(2))
            except ValueError:
                pass

        workshops.append({
            'name': name,
            'search_category': search_cat,
            'city': city_state,
            'district': district if district != 'N/A' else '',
            'plz': plz,
            'sub_category': category,
            'rating': rating,
            'review_count': rev_count,
            'phone': phone,
            'address': address,
            'website': website,
            'photo_url': photo_url,
            'maps_url': maps_url,
            'lat': lat,
            'lng': lng
        })

with open(output_json_path, 'w', encoding='utf-8') as out:
    json.dump(workshops, out, ensure_ascii=False, indent=2)

print(f"Parsed {len(workshops)} valid workshops.")
