<?php
return [
  'key'   => 'jugantor-teal-left-logo',
  'name'  => 'Jugantor Teal (Left Logo • Right Footer)',
  'fields'=> [
    'logo'         => ['type'=>'image', 'label'=>'Logo (bottom-left)', 'default'=>''],
    'title_color'  => ['type'=>'color', 'label'=>'Title Color',         'default'=>'#FFFFFF'],
    'date_color'   => ['type'=>'color', 'label'=>'Date Color',          'default'=>'#FFFFFF'],
    'domain_color' => ['type'=>'color', 'label'=>'Domain Color',        'default'=>'#FFFFFF'],
    'cta_text'     => ['type'=>'text',  'label'=>'CTA Text',            'default'=>'বিস্তারিত কমেন্টে ...'],
    'bg_dark'      => ['type'=>'color', 'label'=>'Dark Base',           'default'=>'#071B24'],
    'grad_black'   => ['type'=>'color', 'label'=>'Gradient Top (navy)','default'=>'#071B24'],
    'grad_red'     => ['type'=>'color', 'label'=>'Gradient Bottom (teal)','default'=>'#12505B'],
    'ad_banner'    => ['type'=>'image', 'label'=>'Ad Banner (1080×150)', 'default'=>''],
    'ad_bg'        => ['type'=>'color', 'label'=>'Ad Background',        'default'=>'#FFFFFF'],
  ],

  'css' =>
'/* Canvas & layout vars */
.jgt-card{position:relative;width:1080px;height:1080px;background:{{opt.bg_dark}};
  overflow:hidden;font-family:"SolaimanLipi",sans-serif;color:#fff;
  --top-h:640px; --footer-h:100px; --banner-h:0px;}
.jgt-card:not([data-banner=""]){--top-h:600px; --banner-h:150px;}

/* Top photo with deep fade */
.jgt-top{position:absolute;left:0;right:0;top:0;height:var(--top-h);background:{{opt.bg_dark}}}
.jgt-photo{position:absolute;inset:0;overflow:hidden}
.jgt-photo img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}
.jgt-photo::after{content:"";position:absolute;left:0;right:0;bottom:0;height:30%;
  background:linear-gradient(180deg, rgba(7,27,36,0) 0%, rgba(7,27,36,.92) 85%, {{opt.bg_dark}} 100%);}
.jgt-date{position:absolute;left:50%;bottom:20px;transform:translateX(-50%);
  font-size:32px;line-height:1;font-weight:700;color:{{opt.date_color}}}

/* Gradient base spans behind footer too */
.jgt-base{position:absolute;left:0;right:0;top:var(--top-h);bottom:0;margin-top:-1px;
  background:linear-gradient(180deg, {{opt.grad_black}} 0%, {{opt.grad_red}} 100%);}

/* Title centered in the true gap (photo→footer) */
.jgt-center{position:absolute;left:0;right:0;top:0;bottom:calc(var(--footer-h) + var(--banner-h));
  display:flex;align-items:center;justify-content:center;padding:0 64px}
.jgt-title{margin:0;text-align:center;color:{{opt.title_color}};font-weight:800;
  line-height:1.20;max-width:960px;font-size:55px}  /* default (>8 words) */

/* Word-count buckets (PASS opt.wc) */
.jgt-card[data-wc="0"] .jgt-title,
.jgt-card[data-wc="1"] .jgt-title,
.jgt-card[data-wc="2"] .jgt-title,
.jgt-card[data-wc="3"] .jgt-title,
.jgt-card[data-wc="4"] .jgt-title,
.jgt-card[data-wc="5"] .jgt-title,
.jgt-card[data-wc="6"] .jgt-title,
.jgt-card[data-wc="7"] .jgt-title,
.jgt-card[data-wc="8"] .jgt-title{font-size:70px}

/* Ad-mode nudges */
.jgt-card:not([data-banner=""])[data-wc="0"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="1"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="2"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="3"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="4"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="5"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="6"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="7"] .jgt-title,
.jgt-card:not([data-banner=""])[data-wc="8"] .jgt-title{font-size:65px}
.jgt-card:not([data-banner=""]) .jgt-title{font-size:50px} /* >8 words with ad */

/* Footer overlays the gradient */
.jgt-footer{position:absolute;left:0;right:0;bottom:var(--banner-h);height:var(--footer-h);
  display:flex;align-items:flex-end;justify-content:space-between;padding:0 44px;background:transparent}
.jgt-right{display:flex;flex-direction:column;align-items:flex-end;text-align:right;gap:8px;margin-bottom:18px}
.jgt-cta{font-size:25px;font-weight:700}
.jgt-domain{font-size:27px;font-weight:800;color:{{opt.domain_color}};letter-spacing:.35px}
.jgt-logo img{height:90px;width:auto;display:block;margin-bottom:10px}

/* Ad banner */
.jgt-banner{position:absolute;left:0;right:0;bottom:0;height:150px;display:none;
  align-items:center;justify-content:center;background:{{opt.ad_bg}};
  z-index:10000;border-top:1px solid #ddd;overflow:hidden}
.jgt-banner img{width:100%;height:100%;object-fit:cover;display:block}
.jgt-card:not([data-banner=""]) .jgt-banner{display:flex}
',

  'html' =>
'<div class="jgt-card" data-wc="{{opt.wc}}" data-banner="{{opt.ad_banner}}">
  <!-- Photo + date -->
  <div class="jgt-top">
    <div class="jgt-photo"><img src="{{image}}" alt="{{title}}" crossOrigin="anonymous" /></div>
    <div class="jgt-date" data-udnpc-text="date">{{date}}</div>
  </div>

  <!-- Gradient base (behind footer too) -->
  <div class="jgt-base">
    <!-- Title centered between photo bottom and footer top -->
    <div class="jgt-center">
      <div class="jgt-title" data-udnpc-text="title">{{title}}</div>
    </div>
  </div>

  <!-- Footer row -->
  <div class="jgt-footer">
    <div class="jgt-logo"><img src="{{opt.logo}}" alt="logo" crossOrigin="anonymous" /></div>
    <div class="jgt-right">
      <div class="jgt-cta" data-udnpc-text="cta">{{opt.cta_text}}</div>
      <div class="jgt-domain" data-udnpc-text="site">{{opt.domain}}</div>
    </div>

  </div>

  <!-- Optional Ad -->
  <div class="jgt-banner">
    <img src="{{opt.ad_banner}}" alt="Ad Banner" crossOrigin="anonymous" />
  </div>
</div>'
];
