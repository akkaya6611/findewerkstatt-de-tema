<?php get_header(); ?>

<style>
/* ===========================
   ANA SAYFA - HERO BÖLÜMÜ
=========================== */
.home-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 100px 20px 130px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    contain: paint;
}
/* Parlayan renkli küreler */
.home-hero::before {
    content: '';
    position: absolute;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(139,92,246,0.25) 0%, transparent 70%);
    top: -150px;
    left: -100px;
    border-radius: 50%;
    animation: glow1 9s ease-in-out infinite alternate;
    pointer-events: none;
    will-change: transform;
}
.home-hero::after {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 70%);
    bottom: -100px;
    right: -100px;
    border-radius: 50%;
    animation: glow2 7s ease-in-out infinite alternate;
    pointer-events: none;
    will-change: transform;
}
.hero-glow-mid {
    position: absolute;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(249,115,22,0.2) 0%, transparent 70%);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    animation: glow1 11s ease-in-out infinite alternate-reverse;
    pointer-events: none;
    will-change: transform;
}
@keyframes glow1 {
    0%   { transform: scale(1) translate(0, 0); }
    100% { transform: scale(1.2) translate(5%, 5%); }
}
@keyframes glow2 {
    0%   { transform: scale(1) translate(0, 0); }
    100% { transform: scale(1.15) translate(-5%, -5%); }
}
@media (max-width: 768px) {
    .home-hero::before,
    .home-hero::after,
    .hero-glow-mid {
        display: none !important;
    }
}
.home-hero .container { position: relative; z-index: 2; }

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(249,115,22,0.22), rgba(234,88,12,0.18));
    color: #ffedd5;
    border: 1px solid rgba(249,115,22,0.45);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 28px;
    backdrop-filter: blur(4px);
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.2);
}
.hero-badge i {
    color: #f97316;
}
.home-hero h1 {
    font-size: 58px;
    font-weight: 800;
    line-height: 1.12;
    margin: 0 0 22px;
    letter-spacing: -1.5px;
}
.home-hero h1 .hl {
    background: linear-gradient(90deg, #fdba74 0%, #fb923c 40%, #f97316 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.home-hero p {
    font-size: 18px;
    color: #94a3b8;
    max-width: 560px;
    margin: 0 auto 40px;
    line-height: 1.75;
}

.hero-search {
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 740px;
    margin: 0 auto 48px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 16px;
    padding: 8px 10px;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05);
}
.hero-search .hs-group {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
    position: relative;
}
.hero-search .hs-icon {
    color: #94a3b8;
    font-size: 15px;
    padding-left: 14px;
    flex-shrink: 0;
}
.hero-search .hs-group input,
.hero-search .hs-group select {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 14px 12px;
    color: #ffffff;
    font-size: 15px;
    font-family: inherit;
    min-width: 0;
    width: 100%;
}
.hero-search .hs-group input::placeholder {
    color: #94a3b8;
}
.hero-search .hs-group select {
    cursor: pointer;
}
.hero-search .hs-group select option {
    background: #0f172a;
    color: #ffffff;
}
.hero-search .hs-divider {
    width: 1px;
    height: 32px;
    background: rgba(255, 255, 255, 0.15);
    margin: 0 4px;
    flex-shrink: 0;
}
.hero-search button {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 14px 28px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
    flex-shrink: 0;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(249, 115, 22, 0.45);
}
.hero-search button:hover {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(249, 115, 22, 0.6);
}

/* ===========================
   HERO STATS - HAREKETLİ & ETKİLEŞİMLİ
=========================== */
.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    max-width: 960px;
    margin: 0 auto;
    position: relative;
    z-index: 5;
}

.hero-stat-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* Hover Efekti & Parlama */
.hero-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
    transform: skewX(-25deg);
    transition: none;
    pointer-events: none;
}

.hero-stat-card:hover {
    transform: translateY(-5px) scale(1.02);
    border-color: rgba(249, 115, 22, 0.6);
    background: rgba(249, 115, 22, 0.1);
    box-shadow: 0 14px 30px rgba(249, 115, 22, 0.25), 0 0 0 1px rgba(249, 115, 22, 0.3);
}

.hero-stat-card:hover::before {
    left: 200%;
    transition: all 0.75s ease-in-out;
}

/* İkon Sarmalayıcıları ve Animasyonları */
.stat-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.hero-stat-card:hover .stat-icon-wrap {
    transform: scale(1.12);
}

.stat-icon-users {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.25), rgba(234, 88, 12, 0.45));
    color: #fb923c;
    border: 1px solid rgba(249, 115, 22, 0.4);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25);
}

.stat-icon-users i {
    animation: userPulse 3s ease-in-out infinite;
}

@keyframes userPulse {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.stat-icon-wrench {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.45));
    color: #fcd34d;
    border: 1px solid rgba(245, 158, 11, 0.4);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
}

.stat-icon-wrench i {
    display: inline-block;
    animation: wrenchSwing 3s ease-in-out infinite;
    transform-origin: 75% 75%;
}

@keyframes wrenchSwing {
    0%, 100% { transform: rotate(0deg); }
    15% { transform: rotate(-20deg); }
    30% { transform: rotate(14deg); }
    45% { transform: rotate(-10deg); }
    60% { transform: rotate(0deg); }
}

.stat-icon-star {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.25), rgba(245, 158, 11, 0.45));
    color: #fde047;
    border: 1px solid rgba(251, 191, 36, 0.4);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.25);
}

.stat-icon-star i {
    animation: starPulse 2.5s ease-in-out infinite;
}

@keyframes starPulse {
    0%, 100% { transform: scale(1); filter: drop-shadow(0 0 2px rgba(253, 224, 71, 0.4)); }
    50% { transform: scale(1.18); filter: drop-shadow(0 0 8px rgba(253, 224, 71, 0.9)); }
}

.stat-icon-shield {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(16, 185, 129, 0.45));
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.4);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
}

.stat-icon-shield i {
    animation: shieldBreath 3s ease-in-out infinite;
}

@keyframes shieldBreath {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

/* Sayı ve Başlık Yazıları */
.stat-content {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.stat-number {
    font-size: 22px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
    display: flex;
    align-items: baseline;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
    min-width: 68px;
    contain: layout inline-size;
}

.stat-number .stat-plus,
.stat-number .stat-prefix {
    color: #f97316;
    font-weight: 800;
    margin-left: 2px;
}

.stat-number .stat-prefix {
    margin-left: 0;
    margin-right: 2px;
}

.stat-number .stat-sub {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.65);
    font-weight: 600;
    margin-left: 2px;
}

.stat-label {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 2px;
    white-space: nowrap;
}

/* ===========================
   BÖLÜM BAŞLIKLARI
=========================== */
.section-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 36px;
}
.section-head h2 {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
    letter-spacing: -0.5px;
}
.section-head p { margin: 0; color: #64748b; font-size: 15px; }
.section-head a {
    font-size: 14px;
    font-weight: 700;
    color: #c2410c;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.section-head a:hover {
    color: #9a3412;
    text-decoration: underline;
}

/* ===========================
   KATEGORİ SLİDER
=========================== */
.categories-section {
    background: white;
    padding: 72px 0;
    border-bottom: 1px solid #e2e8f0;
}
.swiper-cat .swiper-slide {
    width: auto;
}
/* Swiper'ın overflow gizlemesi hover'ı kesiyor — bunu açıyoruz */
.swiper-cat {
    overflow: visible !important;
    padding: 8px 4px 16px;
}
.cat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 36px 24px 32px;
    width: 170px;
    min-height: 180px;
    transition: all 0.25s ease;
    color: #0f172a;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.cat-card:hover {
    border-color: #f97316;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    box-shadow: 0 12px 28px rgba(249, 115, 22, 0.22);
    color: #ea580c;
    /* translateY kaldırıldı — swiper overflow:hidden ile çakışıyordu */
}
.cat-card .cat-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    font-size: 26px;
    color: white;
    transition: transform 0.25s, box-shadow 0.25s;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
}
.cat-card:hover .cat-icon {
    transform: scale(1.08);
    background: linear-gradient(135deg, #ea580c, #c2410c);
    box-shadow: 0 8px 22px rgba(249, 115, 22, 0.5);
}
.cat-card .cat-icon-png {
    width: 34px;
    height: 34px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    transition: transform 0.25s ease;
}
.cat-card:hover .cat-icon-png {
    transform: scale(1.1);
}
.cat-card span {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
}

/* Slider Nav Buttons */
.swiper-nav-btn {
    width: 42px;
    height: 42px;
    background: white;
    border: 1.5px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    color: #475569;
    transition: all 0.2s;
    flex-shrink: 0;
}
.swiper-nav-btn:hover {
    background: #f97316;
    border-color: #f97316;
    color: white;
}
.slider-controls {
    display: flex;
    gap: 10px;
}

/* ===========================
   USTALAR SLİDER
=========================== */
.mechanics-section {
    background: #f8fafc;
    padding: 72px 0;
}
.swiper-mechanics .swiper-slide {
    height: auto;
}
.mech-card {
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    height: 320px;
    display: block;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.25s, box-shadow 0.25s;
}
.mech-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(0,0,0,0.16);
}
.mech-card img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}
.mech-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 20%, rgba(0,0,0,0.5) 55%, rgba(0,0,0,0.95) 100%);
    z-index: 2;
}
.mech-card-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 3;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    backdrop-filter: blur(4px);
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
    letter-spacing: 0.3px;
}
.mech-card-badge i { color: #fcd34d; }
.mech-card-badge.popular {
    background: linear-gradient(135deg, #ef4444, #f97316);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}
.mech-card-views {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(239, 68, 68, 0.85);
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.2px;
}
.mech-card-views i { color: #fef08a; font-size: 11px; }
.mech-card-heart {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 3;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.2s, color 0.2s;
}
.mech-card-heart:hover { transform: scale(1.25); color: #ef4444; }
.mech-card-body {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    z-index: 3;
    color: white;
}
.mech-card-rating {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(4px);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 8px;
}
.mech-card-rating i { color: #fcd34d; font-size: 11px; }
.mech-card h3 {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.3;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mech-card h3 i { color: #22c55e; font-size: 14px; }
.mech-card-loc {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ===========================
   NEDEN BİZ? BÖLÜMÜ
=========================== */
.why-section {
    background: white;
    padding: 72px 0;
}
.why-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}
.why-card {
    text-align: center;
    padding: 36px 24px;
    border-radius: 16px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    transition: all 0.25s;
}
.why-card:hover {
    border-color: #f97316;
    box-shadow: 0 8px 24px rgba(249,115,22,0.18);
}
.why-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 26px;
    color: white;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
}
.why-card h3, .why-card h4 { margin: 0 0 10px; font-size: 17px; font-weight: 700; color: #0f172a; }
.why-card p { margin: 0; font-size: 14px; color: #64748b; line-height: 1.6; }

/* ===========================
   ANA SAYFA - BLOG BÖLÜMÜ
=========================== */
.home-blog-section {
    padding: 72px 0 80px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}
.home-blog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}
.home-blog-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}
.home-blog-card:hover {
    border-color: #f97316;
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(249,115,22,0.18);
}
.home-blog-img-wrap {
    position: relative;
    height: 210px;
    overflow: hidden;
    background: #e2e8f0;
}
.home-blog-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}
.home-blog-card:hover .home-blog-img-wrap img {
    transform: scale(1.06);
}
.home-blog-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.home-blog-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}
.home-blog-cat {
    background: #f0f9ff;
    color: #0369a1;
    font-weight: 700;
    font-size: 12px;
    padding: 3px 9px;
    border-radius: 6px;
}
.home-blog-body h3 {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 10px;
}
.home-blog-body h3 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.home-blog-body h3 a:hover {
    color: #c2410c;
}
.home-blog-body p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 18px;
    flex-grow: 1;
}
.home-blog-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}
.home-blog-readmore {
    color: #c2410c;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: gap 0.2s, color 0.2s;
}
.home-blog-card:hover .home-blog-readmore {
    gap: 10px;
    color: #ea580c;
}

/* ===========================
   RESPONSIVE
=========================== */
@media (max-width: 768px) {
    .home-hero {
        padding: 40px 16px 50px;
    }
    .hero-badge {
        font-size: 12px;
        padding: 6px 16px;
        margin-bottom: 16px;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        background: rgba(249, 115, 22, 0.25) !important;
    }
    .home-hero h1 {
        font-size: 28px;
        line-height: 1.25;
        letter-spacing: -0.5px;
        margin-bottom: 16px;
    }
    .home-hero p {
        font-size: 14.5px;
        line-height: 1.6;
        margin-bottom: 24px;
        padding: 0 8px;
    }
    .hero-search {
        flex-direction: column;
        border-radius: 14px;
        padding: 10px;
        gap: 8px;
        margin-bottom: 28px;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        background: rgba(15, 23, 42, 0.94) !important;
    }
    .hs-divider { display: none; }
    .hero-search .hs-group {
        width: 100%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
    }
    .hero-search button {
        width: 100%;
        justify-content: center;
        padding: 13px;
        font-size: 15px;
    }
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
        max-width: 580px;
        gap: 10px;
    }
    .hero-stat-card {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        background: rgba(15, 23, 42, 0.88) !important;
    }
    .hero-stat-card::before {
        display: none !important;
    }
    .stat-icon-star i,
    .stat-icon-shield i {
        animation: none !important;
    }
    .why-grid { grid-template-columns: repeat(2, 1fr); }
    .home-blog-grid { grid-template-columns: 1fr; }
    .section-head { flex-direction: column; align-items: flex-start; gap: 12px; }
}
@media (max-width: 480px) {
    .home-hero {
        padding: 40px 14px 60px;
    }
    .home-hero h1 {
        font-size: 25px;
        line-height: 1.25;
    }
    .hero-stats {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .hero-stat-card {
        padding: 9px 10px;
        gap: 8px;
        border-radius: 12px;
    }
    .stat-icon-wrap {
        width: 36px;
        height: 36px;
        font-size: 15px;
        border-radius: 9px;
    }
    .stat-number {
        font-size: 17px;
    }
    .stat-label {
        font-size: 11px;
    }
    .why-grid { grid-template-columns: 1fr; }
}

/* ===================================================
   ANA SAYFA AKILLI ARAÇLAR & ACİL ÇÖZÜMLER BANNERLARI
=================================================== */
.home-quick-tools-section {
    padding: 36px 0 20px;
    position: relative;
    z-index: 10;
    margin-top: -30px;
}
.quick-tools-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}
.quick-tool-card {
    border-radius: 20px;
    padding: 28px 30px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}
.quick-tool-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.14);
}
.quick-tool-card.tool-obd {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #311042 100%);
    border: 1.5px solid rgba(249, 115, 22, 0.35);
    color: white;
}
.quick-tool-card.tool-obd:hover {
    border-color: #f97316;
}
.quick-tool-card.tool-tow {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #450a0a 100%);
    border: 1.5px solid rgba(239, 68, 68, 0.4);
    color: white;
}
.quick-tool-card.tool-tow:hover {
    border-color: #ef4444;
}
.tool-content {
    flex: 1;
    z-index: 2;
}
.tool-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.3px;
    margin-bottom: 12px;
    text-transform: uppercase;
}
.badge-orange {
    background: rgba(249, 115, 22, 0.2);
    border: 1px solid rgba(249, 115, 22, 0.45);
    color: #fdba74;
}
.badge-red {
    background: rgba(239, 68, 68, 0.22);
    border: 1px solid rgba(239, 68, 68, 0.5);
    color: #fca5a5;
}
.quick-tool-card h3 {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.35;
    margin: 0 0 8px;
    color: #ffffff;
}
.quick-tool-card p {
    font-size: 13.5px;
    color: #cbd5e1;
    line-height: 1.55;
    margin: 0 0 18px;
    max-width: 420px;
}
.tool-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 800;
    padding: 8px 18px;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.tool-obd .tool-cta-btn {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
}
.tool-tow .tool-cta-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}
.quick-tool-card:hover .tool-cta-btn {
    gap: 12px;
}
.tool-icon-art {
    width: 84px;
    height: 84px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    flex-shrink: 0;
    margin-left: 20px;
    z-index: 2;
    transition: transform 0.3s ease;
}
.art-orange {
    background: rgba(249, 115, 22, 0.15);
    color: #fb923c;
    border: 1px solid rgba(249, 115, 22, 0.3);
}
.art-red {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.quick-tool-card:hover .tool-icon-art {
    transform: scale(1.08) rotate(4deg);
}

/* ===================================================
   ELEKTRİKLİ ARAÇ ŞARJ İSTASYONLARI BANNERI
=================================================== */
.ev-charging-banner-wrap {
    margin: 20px 0 0 0;
    width: 100%;
}
.ev-charging-banner {
    background: linear-gradient(135deg, #022c22 0%, #064e3b 45%, #0d2e26 80%, #0f172a 100%);
    border: 1.5px solid rgba(52, 211, 153, 0.45);
    border-radius: 20px;
    padding: 26px 32px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(6, 78, 59, 0.22);
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}
.ev-charging-banner:hover {
    transform: translateY(-4px);
    border-color: #34d399;
    box-shadow: 0 16px 40px rgba(16, 185, 129, 0.35);
}
.ev-charging-banner::before {
    content: '';
    position: absolute;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
    top: -80px;
    right: 140px;
    border-radius: 50%;
    pointer-events: none;
}
.ev-banner-content {
    flex: 1;
    z-index: 2;
}
.ev-banner-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.ev-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.ev-badge-green {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(52, 211, 153, 0.5);
    color: #a7f3d0;
}
.ev-badge-cyan {
    background: rgba(56, 189, 248, 0.2);
    border: 1px solid rgba(56, 189, 248, 0.5);
    color: #bae6fd;
}
.ev-banner-title {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.35;
    margin: 0 0 8px;
    color: #ffffff;
    letter-spacing: -0.3px;
}
.ev-banner-desc {
    font-size: 13.5px;
    color: #cbd5e1;
    line-height: 1.55;
    margin: 0 0 18px;
    max-width: 720px;
}
.ev-banner-footer {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.ev-btn-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 13px;
    padding: 9px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    transition: all 0.2s ease;
}
.ev-charging-banner:hover .ev-btn-cta {
    gap: 12px;
    background: linear-gradient(135deg, #34d399 0%, #059669 100%);
}
.ev-networks-list {
    display: flex;
    align-items: center;
    gap: 14px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 600;
    flex-wrap: wrap;
}
.ev-networks-list span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ev-networks-list span i {
    color: #34d399;
    font-size: 11px;
}
.ev-banner-visual {
    margin-left: 24px;
    position: relative;
    flex-shrink: 0;
    z-index: 2;
}
.ev-visual-icon {
    width: 86px;
    height: 86px;
    border-radius: 22px;
    background: rgba(16, 185, 129, 0.15);
    border: 1.5px solid rgba(52, 211, 153, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    color: #34d399;
    transition: transform 0.3s ease;
}
.ev-charging-banner:hover .ev-visual-icon {
    transform: scale(1.08) rotate(5deg);
    color: #6ee7b7;
    border-color: #6ee7b7;
}
@media (max-width: 900px) {
    .ev-charging-banner {
        padding: 22px 20px;
    }
    .ev-banner-title {
        font-size: 18px;
    }
    .ev-banner-visual {
        display: none;
    }
    .ev-networks-list {
        display: none;
    }
}

/* 3 YENİ AKILLI SÜRÜCÜ ARACI ALT KARTLARI */
.quick-tools-subgrid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 18px;
}
.quick-sub-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 18px 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    transition: all 0.25s ease;
}
.quick-sub-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.sub-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.sub-fuel .sub-icon-wrap { background: #ecfdf5; color: #059669; }
.sub-chronic .sub-icon-wrap { background: #f5f3ff; color: #7c3aed; }
.sub-accident .sub-icon-wrap { background: #fef2f2; color: #dc2626; }
.sub-fuel:hover { border-color: #10b981; }
.sub-chronic:hover { border-color: #8b5cf6; }
.sub-accident:hover { border-color: #ef4444; }

.sub-content h4 {
    margin: 0 0 4px 0;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
}
.sub-content p {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.4;
}

@media (max-width: 900px) {
    .home-quick-tools-section {
        margin-top: -15px;
        padding: 20px 0 10px;
    }
    .quick-tools-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .quick-tools-subgrid {
        grid-template-columns: 1fr;
        gap: 10px;
        margin-top: 14px;
    }
    .quick-tool-card {
        padding: 22px 20px;
    }
    .tool-icon-art {
        display: none;
    }
    .quick-tool-card h3 {
        font-size: 18px;
    }
}
</style>

<!-- ======================== HERO ======================== -->
<div class="home-hero">
    <div class="hero-glow-mid"></div>
    <div class="container">
        <div class="hero-badge">
            <i class="fa-solid fa-fire"></i>
            Türkiye'nin #1 Numaralı Oto Tamirci Rehberi
        </div>

        <h1>Aracınız İçin <span class="hl">Güvenilir Ustayı</span><br>Hemen Bulun</h1>
        <p>Binlerce onaylı usta arasından aracınızın markasına ve ihtiyacınıza en uygun olanı seçin, yorumları okuyun, hemen iletişime geçin.</p>

        <form action="<?php echo esc_url( home_url('/ustalar') ); ?>" method="get" class="hero-search">
            <input type="hidden" name="post_type" value="mechanic">

            <div class="hs-group">
                <i class="fa-solid fa-magnifying-glass hs-icon"></i>
                <input type="text" name="s" placeholder="Usta adı veya hizmet ara..." aria-label="Usta adı veya hizmet ara">
            </div>

            <div class="hs-divider"></div>

            <div class="hs-group">
                <i class="fa-solid fa-location-dot hs-icon"></i>
                <?php
                $cities = get_terms(array('taxonomy'=>'mechanic_city','hide_empty'=>false,'number'=>81));
                echo '<select name="mechanic_city" aria-label="Şehir Seçiniz">';
                echo '<option value="">Şehir Seç</option>';
                foreach($cities as $c) echo '<option value="'.esc_attr($c->slug).'">'.esc_html($c->name).'</option>';
                echo '</select>';
                ?>
            </div>

            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Usta Bul</button>
        </form>

        <div class="hero-quick-tags" style="display:flex; justify-content:center; align-items:center; gap:10px; margin-top:-28px; margin-bottom:40px; flex-wrap:wrap;">
            <span style="font-size:13px; color:#94a3b8; font-weight:600;"><i class="fa-solid fa-bolt" style="color:#f97316;"></i> Hızlı Erişim:</span>
            <a href="<?php echo esc_url( home_url('/nobetci-oto-tamirciler/') ); ?>" style="display:inline-flex; align-items:center; gap:6px; background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid rgba(239,68,68,0.3); padding:6px 15px; border-radius:20px; font-size:12.5px; font-weight:700; text-decoration:none; transition:all 0.2s;">
                <i class="fa-solid fa-bell" style="color:#ef4444;"></i> Nöbetçi & Pazar Açık Tamirciler
            </a>
            <a href="<?php echo esc_url( home_url('/nobetci-oto-tamirciler/?tip=yol_yardim') ); ?>" style="display:inline-flex; align-items:center; gap:6px; background:rgba(249,115,22,0.15); color:#fdba74; border:1px solid rgba(249,115,22,0.3); padding:6px 15px; border-radius:20px; font-size:12.5px; font-weight:700; text-decoration:none; transition:all 0.2s;">
                <i class="fa-solid fa-truck-pickup" style="color:#f97316;"></i> 7/24 Acil Yol Yardım / Çekici
            </a>
        </div>

        <div class="hero-stats">
            <div class="hero-stat-card">
                <div class="stat-icon-wrap stat-icon-users">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <span class="counter" data-target="10000" data-format="k">10.000</span><span class="stat-plus">+</span>
                    </div>
                    <div class="stat-label">Mutlu Müşteri</div>
                </div>
            </div>

            <div class="hero-stat-card">
                <div class="stat-icon-wrap stat-icon-wrench">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <span class="counter" data-target="2500" data-format="k">2.500</span><span class="stat-plus">+</span>
                    </div>
                    <div class="stat-label">Onaylı Usta</div>
                </div>
            </div>

            <div class="hero-stat-card">
                <div class="stat-icon-wrap stat-icon-star">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <span class="counter" data-target="4.9" data-format="decimal">4.9</span><span class="stat-sub">/5</span>
                    </div>
                    <div class="stat-label">Ortalama Puan</div>
                </div>
            </div>

            <div class="hero-stat-card">
                <div class="stat-icon-wrap stat-icon-shield">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <span class="stat-prefix">%</span><span class="counter" data-target="100" data-format="int">100</span>
                    </div>
                    <div class="stat-label">Güvenli Hizmet</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================== HIZLI ARAÇLAR & ACİL ÇÖZÜMLER BANNER ======================== -->
<section class="home-quick-tools-section">
    <div class="container">
        <div class="quick-tools-grid">
            <!-- BANNER 1: OBD-II VE İKAZ LAMBASI SİHİRBAZI -->
            <a href="<?php echo esc_url( home_url('/ariza-kodlari-ve-gosterge-lambalari') ); ?>" class="quick-tool-card tool-obd">
                <div class="tool-content">
                    <div class="tool-badge badge-orange">
                        <i class="fa-solid fa-microchip"></i>
                        <span>Akıllı Arıza Teşhis</span>
                    </div>
                    <h3>Gösterge Panelinde İkaz Lambası mı Yandı?</h3>
                    <p>14 ikaz lambası ve 50+ OBD-II hata kodunu (P0420, P0300 vb.) saniyeler içinde Türkçe teşhis edin, ilinizdeki uzman ustayı bulun.</p>
                    <span class="tool-cta-btn">
                        <span>Arıza Kodunu Teşhis Et</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
                <div class="tool-icon-art art-orange">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </a>

            <!-- BANNER 2: TEK TIKLA ACİL ÇEKİCİ & YOL YARDIM -->
            <a href="<?php echo esc_url( home_url('/oto-cekici-yol-yardim') ); ?>" class="quick-tool-card tool-tow">
                <div class="tool-content">
                    <div class="tool-badge badge-red">
                        <i class="fa-solid fa-truck-pickup"></i>
                        <span>7/24 Kesintisiz Acil Hizmet</span>
                    </div>
                    <h3>Yolda mı Kaldınız? Tek Tıkla Çekici Çağırın</h3>
                    <p>GPS konumunuzu tek tıkla alın; 81 ilde en yakın açık oto kurtarıcıya WhatsApp üzerinden canlı harita koordinatınızı gönderin.</p>
                    <span class="tool-cta-btn btn-red">
                        <span>En Yakın Çekiciyi Bul & Konum At</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
                <div class="tool-icon-art art-red">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
            </a>
        </div>

        <!-- BANNER: TÜRKİYE ELEKTRİKLİ ARAÇ ŞARJ İSTASYONLARI CANLI HARİTASI -->
        <div class="ev-charging-banner-wrap">
            <a href="<?php echo esc_url( home_url('/elektrikli-sarj-istasyonlari') ); ?>" class="ev-charging-banner">
                <div class="ev-banner-content">
                    <div class="ev-banner-badges">
                        <span class="ev-badge ev-badge-green"><i class="fa-solid fa-bolt"></i> 81 İLDE 760+ İSTASYON</span>
                        <span class="ev-badge ev-badge-cyan"><i class="fa-solid fa-location-crosshairs"></i> CANLI GPS VE NAVİGASYON</span>
                    </div>
                    <h3 class="ev-banner-title">Türkiye Elektrikli Araç Şarj İstasyonları Haritası</h3>
                    <p class="ev-banner-desc">Trugo, Eşarj, ZES, Sharz.net, WAT, Astor ve tüm şarj ağları tek ekranda! Anlık konumunuza en yakın şarj istasyonunu haritada bulun, filtreleyin ve anında rota oluşturun.</p>
                    <div class="ev-banner-footer">
                        <span class="ev-btn-cta">
                            <i class="fa-solid fa-charging-station"></i>
                            <span>Şarj İstasyonlarını Haritada Gör</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                        <div class="ev-networks-list">
                            <span><i class="fa-solid fa-circle-check"></i> Trugo</span>
                            <span><i class="fa-solid fa-circle-check"></i> Eşarj</span>
                            <span><i class="fa-solid fa-circle-check"></i> ZES</span>
                            <span><i class="fa-solid fa-circle-check"></i> Sharz.net</span>
                            <span><i class="fa-solid fa-circle-check"></i> WAT</span>
                        </div>
                    </div>
                </div>
                <div class="ev-banner-visual">
                    <div class="ev-visual-icon">
                        <i class="fa-solid fa-charging-station"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3 YENİ AKILLI SÜRÜCÜ VE ARAÇ ARACI -->
        <div class="quick-tools-subgrid">
            <!-- 1. AKARYAKIT FİYATLARI VE YAKIT HESAPLAYICI -->
            <a href="<?php echo esc_url( home_url('/akaryakit-fiyatlari-ve-rota-hesaplama') ); ?>" class="quick-sub-card sub-fuel">
                <div class="sub-icon-wrap">
                    <i class="fa-solid fa-gas-pump"></i>
                </div>
                <div class="sub-content">
                    <h4>81 İl Akaryakıt & Rota Masrafı</h4>
                    <p>Güncel benzin, motorin, LPG fiyatları ve yol yakıt tüketimi hesaplama motoru.</p>
                </div>
            </a>

            <!-- 2. KRONİK ARIZA & BU ARABA ALINIR MI -->
            <a href="<?php echo esc_url( home_url('/kronik-arizalar-ve-arac-rehberi') ); ?>" class="quick-sub-card sub-chronic">
                <div class="sub-icon-wrap">
                    <i class="fa-solid fa-car-on"></i>
                </div>
                <div class="sub-content">
                    <h4>Kronik Arıza & Araç Rehberi</h4>
                    <p>40+ popüler araç için "Bu araba alınır mı?" karne ve usta ekspertiz tavsiyeleri.</p>
                </div>
            </a>

            <!-- 3. KAZA ANI ASİSTANI & TUTANAK -->
            <a href="<?php echo esc_url( home_url('/kaza-ani-asistani-ve-tutanak') ); ?>" class="quick-sub-card sub-accident">
                <div class="sub-icon-wrap">
                    <i class="fa-solid fa-car-burst"></i>
                </div>
                <div class="sub-content">
                    <h4>Kaza Anı Asistanı & KTT</h4>
                    <p>Adım adım kaza müdahalesi, polis kontrolü, SBM kusur senaryoları ve tutanak.</p>
                </div>
            </a>
        </div>
    </div>
</section>

<?php 
// Google AdSense Banner (Kategori Öncesi - Doğal Akış, Sıfır CLS)
if ( function_exists( 'ototamir_render_ad' ) ) {
    echo ototamir_render_ad( 'header' );
}
?>

<!-- ======================== KATEGORİ SLİDER ======================== -->
<section class="categories-section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Hizmet Kategorileri</h2>
                <p>Aracınızın ihtiyacı olan tüm uzmanlık alanları</p>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                <div class="slider-controls">
                    <button class="swiper-nav-btn" id="cat-prev" aria-label="Önceki Hizmet Kategorileri"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <button class="swiper-nav-btn" id="cat-next" aria-label="Sonraki Hizmet Kategorileri"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <a href="<?php echo esc_url( home_url('/hizmetler') ); ?>">Tümünü Gör →</a>
            </div>
        </div>

        <div class="swiper swiper-cat">
            <div class="swiper-wrapper">
                <?php
                $categories = array(
                    array('slug'=>'mekanik-ustasi',        'icon'=>'fa-gears',           'label'=>'Mekanik Ustaları',    'color'=>'linear-gradient(135deg,#f97316,#ea580c)'),
                    array('slug'=>'oto-elektrik-ustalari', 'icon'=>'fa-bolt',            'label'=>'Oto Elektrik Ustaları', 'color'=>'linear-gradient(135deg,#f59e0b,#ea580c)'),
                    array('slug'=>'kaporta-ustalari',      'icon'=>'fa-car-burst',       'label'=>'Kaporta & Boya',     'color'=>'linear-gradient(135deg,#ea580c,#c2410c)'),
                    array('slug'=>'motor-ustalari',        'icon'=>'fa-oil-can',         'label'=>'Motor Revizyon',     'color'=>'linear-gradient(135deg,#fb923c,#ea580c)'),
                    array('slug'=>'oto-ekspertiz',         'icon'=>'fa-clipboard-check', 'label'=>'Ekspertiz',          'color'=>'linear-gradient(135deg,#f59e0b,#d97706)'),
                    array('slug'=>'oto-klima-ustalari',    'icon'=>'fa-snowflake',       'label'=>'Oto Klima',          'color'=>'linear-gradient(135deg,#fdba74,#f97316)'),
                    array('slug'=>'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim', 'icon'=>'fa-microchip',       'label'=>'Oto Beyin & ECU',    'color'=>'linear-gradient(135deg,#f97316,#dc2626)'),
                    array('slug'=>'periyodik-bakim-servisleri-yag-filtre-genel-kontrol',  'icon'=>'fa-calendar-check',  'label'=>'Periyodik Bakım',    'color'=>'linear-gradient(135deg,#10b981,#059669)'),
                    array('slug'=>'fren-ve-balata-ustasi',    'icon'=>'fa-hand-paper',      'label'=>'Fren & Balata',      'color'=>'linear-gradient(135deg,#ef4444,#ea580c)'),
                    array('slug'=>'rot-balans-ve-on-takim-ustasi',     'icon'=>'fa-rotate',          'label'=>'Rot & Balans',       'color'=>'linear-gradient(135deg,#f97316,#c2410c)'),
                    array('slug'=>'aks-ve-suspansiyon-tamircisi',       'icon'=>'fa-arrows-up-down',  'label'=>'Aks & Süspansiyon',  'color'=>'linear-gradient(135deg,#f59e0b,#ea580c)'),
                    array('slug'=>'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans',    'icon'=>'fa-circle-dot',      'label'=>'Lastik & Jant',      'color'=>'linear-gradient(135deg,#64748b,#334155)'),
                    array('slug'=>'oto-cam-ustalari',      'icon'=>'fa-shield-halved',   'label'=>'Oto Cam Ustaları',   'color'=>'linear-gradient(135deg,#0284c7,#0369a1)'),
                    array('slug'=>'oto-doseme-ustalari',   'icon'=>'fa-couch',           'label'=>'Oto Döşeme',         'color'=>'linear-gradient(135deg,#6366f1,#8b5cf6)'),
                    array('slug'=>'sanziman-ve-guc-aktarma',  'icon'=>'fa-cogs',            'label'=>'Şanzıman',           'color'=>'linear-gradient(135deg,#ea580c,#9a3412)'),
                );
                foreach($categories as $cat):
                    $url = home_url('/ustalar/?service_type='.$cat['slug']);
                ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($url); ?>" class="cat-card">
                        <div class="cat-icon" style="background: <?php echo $cat['color']; ?>;">
                            <img src="<?php echo esc_url( ototamir_get_service_icon( $cat['slug'] ) ); ?>" alt="" aria-hidden="true" class="cat-icon-png" width="34" height="34" loading="lazy" decoding="async">
                        </div>
                        <span><?php echo esc_html($cat['label']); ?></span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ======================== USTALAR SLİDER ======================== -->
<section class="mechanics-section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Son Eklenen Ustalar</h2>
                <p>En yeni ve güncel usta ilanları</p>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                <div class="slider-controls">
                    <button class="swiper-nav-btn" id="mech-prev" aria-label="Önceki Ustalar"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <button class="swiper-nav-btn" id="mech-next" aria-label="Sonraki Ustalar"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <a href="<?php echo esc_url( home_url('/ustalar') ); ?>">Tüm Ustalar →</a>
            </div>
        </div>

        <div class="swiper swiper-mechanics">
            <div class="swiper-wrapper">
                <?php
                $mechs = new WP_Query(array(
                    'post_type'      => 'mechanic',
                    'posts_per_page' => 12,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ));
                if($mechs->have_posts()):
                    while($mechs->have_posts()): $mechs->the_post();
                        $img = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                        if ( empty( $img ) ) {
                            $img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                        }
                        if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                            $img = ototamir_optimize_image_url( $img, 450 );
                        }
                        $rating = get_mechanic_rating_data(get_the_ID());
                        $cities = wp_get_post_terms(get_the_ID(), 'mechanic_city');
                        $districts = wp_get_post_terms(get_the_ID(), 'mechanic_district');
                        $city = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                        $dist = ($districts && !is_wp_error($districts)) ? $districts[0]->name : '';
                        $loc = trim("$dist, $city", ", ");
                        $is_fav = function_exists('is_user_favorite') && is_user_favorite(get_the_ID());
                        $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                        $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                        $card_vip_class = $is_vip ? ' mech-card-vip' : '';
                ?>
                <div class="swiper-slide">
                    <a href="<?php the_permalink(); ?>" class="mech-card<?php echo $card_vip_class; ?>">
                        <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" width="370" height="250" loading="lazy" decoding="async">
                        <div class="mech-card-overlay"></div>

                        <?php if ( $is_vip ) : ?>
                            <div class="mech-card-badge mech-card-badge-vip">
                                <i class="fa-solid fa-crown"></i> <?php echo esc_html( $vip_badge_text ); ?>
                            </div>
                        <?php else : ?>
                            <div class="mech-card-badge">
                                <i class="fa-solid fa-star"></i> Tavsiye Edildi
                            </div>
                        <?php endif; ?>

                        <div class="mech-card-heart listing-heart" data-post-id="<?php echo get_the_ID(); ?>"
                             style="<?php echo $is_fav ? 'color:#ef4444;' : 'color:rgba(255,255,255,0.7);'; ?>">
                            <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                        </div>

                        <div class="mech-card-body">
                            <?php if($rating['count'] > 0): ?>
                                <div class="mech-card-rating">
                                    <i class="fa-solid fa-star"></i> <?php echo $rating['avg']; ?> (<?php echo $rating['count']; ?> yorum)
                                </div>
                            <?php else: ?>
                                <div class="mech-card-rating" style="background:rgba(100,116,139,0.5);">
                                    <i class="fa-regular fa-star"></i> Yeni Üye
                                </div>
                            <?php endif; ?>

                            <h3><?php the_title(); ?> <i class="fa-solid fa-circle-check"></i></h3>
                            <?php if($loc): ?>
                            <div class="mech-card-loc">
                                <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($loc); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ======================== EN ÇOK ZİYARET EDİLEN USTALAR ======================== -->
<section class="mechanics-section popular-section" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 70px 0;">
    <div class="container">
        <div class="section-head">
            <div>
                <div style="display:inline-flex; align-items:center; gap:6px; background:#fef2f2; color:#b91c1c; padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:10px; border:1px solid #fee2e2;">
                    <i class="fa-solid fa-fire"></i> EN ÇOK İNCELENENLER
                </div>
                <h2>En Çok Ziyaret Edilen Ustalar</h2>
                <p>Kullanıcılarımızın bu hafta en çok incelediği ve güvendiği oto tamir servisleri</p>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                <div class="slider-controls">
                    <button class="swiper-nav-btn" id="popular-prev" aria-label="Önceki Popüler Ustalar"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <button class="swiper-nav-btn" id="popular-next" aria-label="Sonraki Popüler Ustalar"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <a href="<?php echo esc_url( home_url('/ustalar') ); ?>">Tüm Ustalar →</a>
            </div>
        </div>

        <div class="swiper swiper-popular">
            <div class="swiper-wrapper">
                <?php
                $popular_mechs = new WP_Query(array(
                    'post_type'      => 'mechanic',
                    'posts_per_page' => 12,
                    'post_status'    => 'publish',
                    'meta_key'       => '_mechanic_views_count',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                ));
                if($popular_mechs->have_posts()):
                    while($popular_mechs->have_posts()): $popular_mechs->the_post();
                        $img = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                        if ( empty( $img ) ) {
                            $img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                        }
                        if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                            $img = ototamir_optimize_image_url( $img, 450 );
                        }
                        $rating = get_mechanic_rating_data(get_the_ID());
                        $cities = wp_get_post_terms(get_the_ID(), 'mechanic_city');
                        $districts = wp_get_post_terms(get_the_ID(), 'mechanic_district');
                        $city = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                        $dist = ($districts && !is_wp_error($districts)) ? $districts[0]->name : '';
                        $loc = trim("$dist, $city", ", ");
                        $is_fav = function_exists('is_user_favorite') && is_user_favorite(get_the_ID());
                        $views = function_exists('ototamir_get_post_views') ? ototamir_get_post_views(get_the_ID()) : '1.250';
                        $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                        $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                        $card_vip_class = $is_vip ? ' mech-card-vip' : '';
                ?>
                <div class="swiper-slide">
                    <a href="<?php the_permalink(); ?>" class="mech-card<?php echo $card_vip_class; ?>">
                        <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" width="370" height="250" loading="lazy" decoding="async">
                        <div class="mech-card-overlay"></div>

                        <?php if ( $is_vip ) : ?>
                            <div class="mech-card-badge mech-card-badge-vip">
                                <i class="fa-solid fa-crown"></i> <?php echo esc_html( $vip_badge_text ); ?>
                            </div>
                        <?php else : ?>
                            <div class="mech-card-badge popular">
                                <i class="fa-solid fa-fire"></i> Popüler Usta
                            </div>
                        <?php endif; ?>

                        <div class="mech-card-heart listing-heart" data-post-id="<?php echo get_the_ID(); ?>"
                             style="<?php echo $is_fav ? 'color:#ef4444;' : 'color:rgba(255,255,255,0.7);'; ?>">
                            <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                        </div>

                        <div class="mech-card-body">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <div class="mech-card-views">
                                    <i class="fa-solid fa-eye"></i> <?php echo esc_html($views); ?> Ziyaret
                                </div>
                                <?php if($rating['count'] > 0): ?>
                                    <div class="mech-card-rating">
                                        <i class="fa-solid fa-star"></i> <?php echo $rating['avg']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h3><?php the_title(); ?> <i class="fa-solid fa-circle-check"></i></h3>
                            <?php if($loc): ?>
                            <div class="mech-card-loc">
                                <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($loc); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ======================== NEDEN BİZ? ======================== -->
<section class="why-section">
    <div class="container">
        <div class="section-head" style="justify-content:center; text-align:center; display:block; margin-bottom:48px;">
            <h2 style="font-size:34px;">Neden OtoTamirciBul?</h2>
            <p style="margin-top:8px; font-size:16px;">Bizi milyonların tercih ettiği özellikleri</p>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Onaylı Ustalar</h3>
                <p>Her usta admin onayından geçer. Sahte ilan yoktur.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa-solid fa-star"></i></div>
                <h3>Gerçek Yorumlar</h3>
                <p>Yorumlar gerçek müşteriler tarafından yazılır, düzenlenemez.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <h3>Kolay Arama</h3>
                <p>İl, ilçe, marka ve hizmet türüne göre saniyeler içinde filtrele.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fa-solid fa-building"></i></div>
                <h3>Ücretsiz Firma Kaydı</h3>
                <p>İşletmenizi ücretsiz ekleyin, binlerce yeni araç sahibine anında ulaşın.</p>
            </div>
        </div>
    </div>
</section>

<!-- ======================== FAYDALI MAKALELER (BLOG) ======================== -->
<section class="home-blog-section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Faydalı Makaleler & İpuçları</h2>
                <p>Aracınızın bakımı, arıza teşhisi ve tasarruf rehberleri</p>
            </div>
            <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">Tüm Yazıları Gör →</a>
        </div>

        <div class="home-blog-grid">
            <?php
            $home_blog = new WP_Query(array(
                'post_type'      => 'post',
                'posts_per_page' => 3,
                'post_status'    => 'publish'
            ));

            if($home_blog->have_posts()):
                while($home_blog->have_posts()): $home_blog->the_post();
                    $img = function_exists('ototamir_get_smart_post_image') 
                        ? ototamir_get_smart_post_image(get_the_ID(), 450, 260) 
                        : (get_post_meta(get_the_ID(), '_custom_blog_image', true) ?: (get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=450&q=75'));
                    $cats = get_the_category();
                    $cat_name = !empty($cats) ? $cats[0]->name : 'Oto Rehber';
                    $read_time = get_post_meta(get_the_ID(), '_blog_read_time', true) ?: '4 dk okuma';
            ?>
            <article class="home-blog-card">
                <div class="home-blog-img-wrap">
                    <a href="<?php the_permalink(); ?>">
                        <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" width="370" height="210" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                    </a>
                </div>
                <div class="home-blog-body">
                    <div class="home-blog-meta">
                        <span class="home-blog-cat"><?php echo esc_html($cat_name); ?></span>
                        <span><i class="fa-regular fa-clock"></i> <?php echo esc_html($read_time); ?></span>
                    </div>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p><?php echo wp_trim_words(get_the_excerpt() ?: get_the_content(), 16, '...'); ?></p>
                    <div class="home-blog-footer">
                        <span style="font-size:13px; color:#475569;"><?php echo get_the_date('j F Y'); ?></span>
                        <a href="<?php the_permalink(); ?>" class="home-blog-readmore">
                            <span>Devamını Oku</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
</section>

<script>
// Zorunlu Yeniden Düzenlemeyi (Forced Reflow / Layout Thrashing) Tamamen Önleme
function initSliderWhenReady(selector, options, onInit) {
    const el = document.querySelector(selector);
    if (!el || el._swiperInitDone) return;

    function doInit() {
        if (typeof Swiper === 'undefined') {
            setTimeout(doInit, 50);
            return;
        }
        if (el._swiperInitDone) return;
        el._swiperInitDone = true;

        requestAnimationFrame(() => {
            const swiperOpts = Object.assign({
                observer: true,
                observeParents: true,
                resizeObserver: true,
                watchOverflow: true,
            }, options);
            const instance = new Swiper(el, swiperOpts);
            if (typeof onInit === 'function') onInit(instance);
        });
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                observer.disconnect();
                doInit();
            }
        }, { rootMargin: '350px 0px' });
        observer.observe(el);
    } else {
        doInit();
    }
}

function initAllSliders() {
    // 1. Kategori Slider
    initSliderWhenReady('.swiper-cat', {
        slidesPerView: 'auto',
        spaceBetween: 16,
        freeMode: true,
        grabCursor: true,
        navigation: { nextEl: '#cat-next', prevEl: '#cat-prev' },
    }, (catSwiper) => {
        const catPrev = document.getElementById('cat-prev');
        const catNext = document.getElementById('cat-next');
        if (catPrev) catPrev.addEventListener('click', () => catSwiper.slidePrev());
        if (catNext) catNext.addEventListener('click', () => catSwiper.slideNext());
    });

    // 2. Usta Slider (Görünür alana yaklaşınca yüklenir)
    initSliderWhenReady('.swiper-mechanics', {
        slidesPerView: 1.2,
        spaceBetween: 20,
        grabCursor: true,
        navigation: { nextEl: '#mech-next', prevEl: '#mech-prev' },
        breakpoints: {
            640:  { slidesPerView: 2.2 },
            900:  { slidesPerView: 3.1 },
            1200: { slidesPerView: 4 },
        },
    }, (mechSwiper) => {
        const mPrev = document.getElementById('mech-prev');
        const mNext = document.getElementById('mech-next');
        if (mPrev) mPrev.addEventListener('click', () => mechSwiper.slidePrev());
        if (mNext) mNext.addEventListener('click', () => mechSwiper.slideNext());
    });

    // 3. Popüler Usta Slider (Görünür alana yaklaşınca yüklenir)
    initSliderWhenReady('.swiper-popular', {
        slidesPerView: 1.2,
        spaceBetween: 20,
        grabCursor: true,
        navigation: { nextEl: '#popular-next', prevEl: '#popular-prev' },
        breakpoints: {
            640:  { slidesPerView: 2.2 },
            900:  { slidesPerView: 3.1 },
            1200: { slidesPerView: 4 },
        },
    }, (popularSwiper) => {
        const popPrev = document.getElementById('popular-prev');
        const popNext = document.getElementById('popular-next');
        if (popPrev) popPrev.addEventListener('click', () => popularSwiper.slidePrev());
        if (popNext) popNext.addEventListener('click', () => popularSwiper.slideNext());
    });
}

// DOM hazır olduğunda Observer'ları başlat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllSliders);
} else {
    initAllSliders();
}

document.addEventListener('DOMContentLoaded', function() {

    // Hero Sayaç Animasyonu (Count-Up)
    const statCounters = document.querySelectorAll('.hero-stats .counter[data-target]');
    if (statCounters.length) {
        let hasRun = false;
        const animateCounters = () => {
            if (hasRun) return;
            hasRun = true;

            statCounters.forEach(el => {
                const target = parseFloat(el.getAttribute('data-target'));
                const format = el.getAttribute('data-format') || 'int';
                const duration = 1800;
                const start = performance.now();

                const updateCount = (now) => {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease-out cubic
                    const ease = 1 - Math.pow(1 - progress, 3);
                    const current = target * ease;

                    if (format === 'decimal') {
                        el.textContent = current.toFixed(1);
                    } else if (format === 'k') {
                        el.textContent = Math.round(current).toLocaleString('tr-TR');
                    } else {
                        el.textContent = Math.round(current);
                    }

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
                        if (format === 'decimal') {
                            el.textContent = target.toFixed(1);
                        } else if (format === 'k') {
                            el.textContent = target.toLocaleString('tr-TR');
                        } else {
                            el.textContent = target;
                        }
                    }
                };
                requestAnimationFrame(updateCount);
            });
        };

        if ('IntersectionObserver' in window) {
            const statsSection = document.querySelector('.hero-stats');
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    animateCounters();
                    observer.disconnect();
                }
            }, { threshold: 0.2 });
            if (statsSection) observer.observe(statsSection);
        } else {
            animateCounters();
        }
    }
});
</script>

<?php get_footer(); ?>
