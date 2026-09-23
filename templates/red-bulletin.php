<?php
return [
    'key' => 'red-bulletin',
    'name' => 'Red Bulletin',
    'fields' => [
        'logo' => ['type'=>'image', 'label'=>'Logo (top-right)', 'default'=>''],
        'accent' => ['type'=>'color', 'label'=>'Bulletin red', 'default'=>'#CE283D'],
        'paper' => ['type'=>'color', 'label'=>'Headline background', 'default'=>'#FFFFFF'],
        'ink' => ['type'=>'color', 'label'=>'Headline color', 'default'=>'#18202D'],
        'on_accent' => ['type'=>'color', 'label'=>'Header and footer text', 'default'=>'#FFFFFF'],
        'section_text' => ['type'=>'text', 'label'=>'Bulletin label', 'default'=>'সংবাদ আপডেট'],
        'cta_text' => ['type'=>'text', 'label'=>'Footer text', 'default'=>'বিস্তারিত কমেন্টে'],
    ],
    'css' => '
.nrb-card{position:relative;width:1080px;height:1080px;overflow:hidden;background:{{opt.accent}};color:{{opt.ink}};font-family:"SolaimanLipi",sans-serif;text-align:left}
.nrb-card *{box-sizing:border-box}
.nrb-header{position:absolute;top:28px;left:44px;right:44px;height:84px;display:flex;align-items:center;justify-content:space-between;gap:30px;color:{{opt.on_accent}}}
.nrb-label{max-width:55%;font-size:42px;font-weight:700;line-height:1.15;overflow-wrap:anywhere;border-left:6px solid {{opt.on_accent}};padding-left:20px}
.nrb-label:empty{display:none}
.nrb-brand{margin-left:auto;max-width:42%;font-size:34px;line-height:1.2;text-align:right;overflow-wrap:anywhere}
.nrb-brand img{display:block;max-width:260px;max-height:76px;width:auto;height:auto;padding:8px 14px;background:#fff;border-radius:4px}
.nrb-card[data-logo=""] .nrb-brand img,.nrb-card:not([data-logo=""]) .nrb-brand span{display:none}
.nrb-photo{position:absolute;top:140px;left:0;right:0;height:474px;background-color:#D7DFE6;background-image:url("{{image}}");background-size:cover;background-position:center}
.nrb-date{position:absolute;left:44px;bottom:26px;padding:12px 20px;background:{{opt.paper}};color:{{opt.ink}};max-width:85%;font-size:27px;line-height:1.2}
.nrb-story{position:absolute;left:28px;right:28px;top:590px;bottom:106px;background:{{opt.paper}};border-top:8px solid {{opt.accent}};padding:28px 40px;display:flex;align-items:center}
.nrb-title{font-size:61px;line-height:1.22;font-weight:700;margin:0;overflow-wrap:anywhere}
.nrb-card[data-wc="1"] .nrb-title,.nrb-card[data-wc="2"] .nrb-title,.nrb-card[data-wc="3"] .nrb-title,.nrb-card[data-wc="4"] .nrb-title,.nrb-card[data-wc="5"] .nrb-title,.nrb-card[data-wc="6"] .nrb-title,.nrb-card[data-wc="7"] .nrb-title,.nrb-card[data-wc="8"] .nrb-title{font-size:78px}
.nrb-footer{position:absolute;left:44px;right:44px;bottom:28px;min-height:50px;display:flex;align-items:center;justify-content:space-between;gap:24px;color:{{opt.on_accent}};line-height:1.2}
.nrb-cta{font-size:28px;max-width:53%;overflow-wrap:anywhere}
.nrb-site{font-size:26px;max-width:45%;text-align:right;overflow-wrap:anywhere}
',
    'html' => '
<div class="nrb-card" data-logo="{{opt.logo}}" data-wc="{{opt.wc}}">
  <div class="nrb-header"><div class="nrb-label">{{opt.section_text}}</div><div class="nrb-brand"><img src="{{opt.logo}}" alt="{{site}}" crossorigin="anonymous"><span>{{site}}</span></div></div>
  <div class="nrb-photo" role="img" aria-label="{{title}}"><div class="nrb-date" data-udnpc-text="date">{{date}}</div></div>
  <div class="nrb-story"><div class="nrb-title" data-udnpc-text="title">{{title}}</div></div>
  <div class="nrb-footer"><div class="nrb-cta" data-udnpc-text="cta">{{opt.cta_text}}</div><div class="nrb-site" data-udnpc-text="site">{{opt.domain}}</div></div>
</div>',
];
