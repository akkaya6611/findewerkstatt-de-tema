<!-- Nasıl Çalışırız Alanı -->
<div class="how-it-works-section" style="max-width: 1200px; margin: 80px auto; padding: 0 15px; display: flex; flex-wrap: wrap; align-items: center; gap: 40px;">
    
    <!-- Sol Kısım: Yazılar ve İkonlar -->
    <div style="flex: 1; min-width: 300px;">
        <h2 style="font-size: 32px; color: #333; margin-bottom: 40px; position: relative; padding-bottom: 15px;">
            Nasıl Çalışırız?
            <span style="position: absolute; bottom: 0; left: 0; width: 50px; height: 3px; background-color: #f97316;"></span>
        </h2>
        
        <div class="hiw-step" style="display: flex; margin-bottom: 30px; align-items: flex-start;">
            <div class="hiw-icon" style="flex-shrink: 0; width: 70px; height: 70px; border-radius: 50%; background-color: #fff7ed; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
            </div>
            <div>
                <h4 style="font-size: 18px; margin: 0 0 10px 0; color: #333;">Ustalar Hesaplarını Oluşturur!</h4>
                <p style="color: #666; margin: 0; line-height: 1.5;">Ustalarımız kendi profillerini oluşturur ve kendi firmalarını eklerler.</p>
            </div>
        </div>
        
        <div class="hiw-step" style="display: flex; margin-bottom: 30px; align-items: flex-start;">
            <div class="hiw-icon" style="flex-shrink: 0; width: 70px; height: 70px; border-radius: 50%; background-color: #fff7ed; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div>
                <h4 style="font-size: 18px; margin: 0 0 10px 0; color: #333;">İlanları Kontrol Edilir.</h4>
                <p style="color: #666; margin: 0; line-height: 1.5;">İlanlar kontrol edilir eksiklikler giderilir ve tarafımızca onaylanır.</p>
            </div>
        </div>

        <div class="hiw-step" style="display: flex; margin-bottom: 30px; align-items: flex-start;">
            <div class="hiw-icon" style="flex-shrink: 0; width: 70px; height: 70px; border-radius: 50%; background-color: #fff7ed; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </div>
            <div>
                <h4 style="font-size: 18px; margin: 0 0 10px 0; color: #333;">Türkiye'nin her yerinden sitemizdeki ziyaretçilere</h4>
                <p style="color: #666; margin: 0; line-height: 1.5;">firmaları gösterilir. En yakındaki usta ziyaretçilerimize gösterilir.</p>
            </div>
        </div>
    </div>
    
    <!-- Sağ Kısım: İllüstrasyon (Dahili SVG - Kırık link olmaz) -->
    <div style="flex: 1; min-width: 300px; text-align: center; display: flex; justify-content: center; align-items: center;">
        <svg viewBox="0 0 500 400" width="100%" max-width="480" height="auto" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="hiw-grad-bg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#fff7ed"/>
                    <stop offset="100%" stop-color="#ffedd5"/>
                </linearGradient>
                <linearGradient id="hiw-grad-orange" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f97316"/>
                    <stop offset="100%" stop-color="#ea580c"/>
                </linearGradient>
                <linearGradient id="hiw-grad-blue" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0284c7"/>
                    <stop offset="100%" stop-color="#0369a1"/>
                </linearGradient>
                <filter id="hiw-shadow" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="0" dy="8" stdDeviation="12" flood-opacity="0.08"/>
                </filter>
            </defs>
            <!-- Background Blob -->
            <rect x="25" y="25" width="450" height="350" rx="32" fill="url(#hiw-grad-bg)"/>
            <circle cx="410" cy="80" r="50" fill="#fed7aa" opacity="0.4"/>
            <circle cx="90" cy="320" r="60" fill="#fdba74" opacity="0.3"/>
            <!-- Main Search Window Card -->
            <rect x="70" y="70" width="360" height="260" rx="20" fill="#ffffff" filter="url(#hiw-shadow)"/>
            <!-- Window Header -->
            <rect x="70" y="70" width="360" height="42" rx="20" fill="#f8fafc"/>
            <circle cx="96" cy="91" r="5" fill="#ef4444"/>
            <circle cx="112" cy="91" r="5" fill="#f59e0b"/>
            <circle cx="128" cy="91" r="5" fill="#10b981"/>
            <!-- Search Bar Mockup -->
            <rect x="150" y="80" width="260" height="22" rx="11" fill="#e2e8f0"/>
            <circle cx="164" cy="91" r="5" fill="#94a3b8"/>
            <rect x="176" y="88" width="80" height="6" rx="3" fill="#cbd5e1"/>
            <!-- Content Area: Car & Mechanic Graphic -->
            <!-- Car Silhouette -->
            <path d="M120 220 L150 165 L260 165 L300 220 L360 220 C368 220 375 227 375 235 L375 255 C375 260 370 265 365 265 L115 265 C110 265 105 260 105 255 L105 235 C105 227 112 220 120 220 Z" fill="url(#hiw-grad-orange)"/>
            <!-- Car Windows -->
            <path d="M155 173 L205 173 L205 212 L132 212 Z" fill="#ffffff" opacity="0.85"/>
            <path d="M215 173 L255 173 L290 212 L215 212 Z" fill="#ffffff" opacity="0.85"/>
            <!-- Wheels -->
            <circle cx="155" cy="265" r="24" fill="#1e293b"/>
            <circle cx="155" cy="265" r="10" fill="#e2e8f0"/>
            <circle cx="325" cy="265" r="24" fill="#1e293b"/>
            <circle cx="325" cy="265" r="10" fill="#e2e8f0"/>
            <!-- Pin Marker Floating -->
            <g transform="translate(230, 95)">
                <path d="M20 0 C8.95 0 0 8.95 0 20 C0 35 20 54 20 54 C20 54 40 35 40 20 C40 8.95 31.05 0 20 0 Z" fill="#ea580c"/>
                <circle cx="20" cy="20" r="8" fill="#ffffff"/>
            </g>
            <!-- Badge 81 İl -->
            <rect x="90" y="125" width="105" height="30" rx="15" fill="#ecfdf5"/>
            <text x="142" y="145" font-size="12" font-weight="bold" fill="#059669" text-anchor="middle" font-family="sans-serif">81 İl & İlçe</text>
            <!-- Badge Güvenilir Usta -->
            <rect x="290" y="125" width="125" height="30" rx="15" fill="#eff6ff"/>
            <text x="352" y="145" font-size="12" font-weight="bold" fill="#0284c7" text-anchor="middle" font-family="sans-serif">Doğrulanmış Usta</text>
        </svg>
    </div>
</div>
