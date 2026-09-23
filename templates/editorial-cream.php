<?php
return [
    'key' => 'editorial-cream',
    'name' => 'Editorial Cream',
    'fields' => [
        'logo' => ['type'=>'image', 'label'=>'Masthead logo', 'default'=>''],
        'paper' => ['type'=>'color', 'label'=>'Paper background', 'default'=>'#F5F0E6'],
        'ink' => ['type'=>'color', 'label'=>'Headline and branding', 'default'=>'#202C29'],
        'accent' => ['type'=>'color', 'label'=>'Editorial accent', 'default'=>'#BA4934'],
        'muted' => ['type'=>'color', 'label'=>'Date and website', 'default'=>'#59635B'],
        'section_text' => ['type'=>'text', 'label'=>'Section label', 'default'=>'বিশেষ প্রতিবেদন'],
        'cta_text' => ['type'=>'text', 'label'=>'Footer text', 'default'=>'বিস্তারিত পড়ুন কমেন্টে'],
    ],
    'css' => '
.nec-card{position:relative;width:1080px;height:1080px;overflow:hidden;background:{{opt.paper}};color:{{opt.ink}};font-family:"SolaimanLipi",sans-serif;text-align:left}
.nec-card *{box-sizing:border-box}
.nec-header{position:absolute;top:34px;left:48px;right:48px;height:94px;border-bottom:2px solid {{opt.ink}};display:flex;align-items:center;justify-content:space-between;gap:30px;padding-bottom:18px}
.nec-brand{max-width:55%;font-size:36px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
.nec-brand img{display:block;max-width:300px;max-height:68px;width:auto;height:auto}
.nec-card[data-logo=""] .nec-brand img,.nec-card:not([data-logo=""]) .nec-brand span{display:none}
.nec-date{font-size:25px;color:{{opt.muted}};text-align:right;max-width:43%;line-height:1.2}
.nec-photo{position:absolute;top:154px;left:48px;right:48px;height:476px;background-color:#D3DAD2;background-image:url("{{image}}");background-size:cover;background-position:center}
.nec-label{position:absolute;left:0;bottom:0;max-width:85%;padding:12px 24px;background:{{opt.accent}};color:#fff;font-size:28px;line-height:1.2;overflow-wrap:anywhere}
.nec-label:empty{display:none}
.nec-story{position:absolute;top:658px;left:48px;right:48px;bottom:120px;display:flex;align-items:center;border-left:6px solid {{opt.accent}};padding-left:28px}
.nec-title{margin:0;font-size:60px;line-height:1.22;font-weight:700;overflow-wrap:anywhere}
.nec-card[data-wc="1"] .nec-title,.nec-card[data-wc="2"] .nec-title,.nec-card[data-wc="3"] .nec-title,.nec-card[data-wc="4"] .nec-title,.nec-card[data-wc="5"] .nec-title,.nec-card[data-wc="6"] .nec-title,.nec-card[data-wc="7"] .nec-title,.nec-card[data-wc="8"] .nec-title{font-size:76px}
.nec-footer{position:absolute;bottom:30px;left:48px;right:48px;min-height:58px;border-top:2px solid {{opt.ink}};padding-top:18px;display:flex;align-items:center;justify-content:space-between;gap:24px;line-height:1.2}
.nec-cta{font-size:26px;color:{{opt.accent}};max-width:53%;overflow-wrap:anywhere}
.nec-site{font-size:25px;color:{{opt.muted}};max-width:45%;text-align:right;overflow-wrap:anywhere}
',
    'html' => '
<div class="nec-card" data-logo="{{opt.logo}}" data-wc="{{opt.wc}}">
  <div class="nec-header"><div class="nec-brand"><img src="{{opt.logo}}" alt="{{site}}" crossorigin="anonymous"><span>{{site}}</span></div><div class="nec-date" data-udnpc-text="date">{{date}}</div></div>
  <div class="nec-photo" role="img" aria-label="{{title}}"><div class="nec-label">{{opt.section_text}}</div></div>
  <div class="nec-story"><div class="nec-title" data-udnpc-text="title">{{title}}</div></div>
  <div class="nec-footer"><div class="nec-cta" data-udnpc-text="cta">{{opt.cta_text}}</div><div class="nec-site" data-udnpc-text="site">{{opt.domain}}</div></div>
</div>',
];
