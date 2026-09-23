<?php
return [
    'key' => 'split-dispatch',
    'name' => 'Split Dispatch',
    'fields' => [
        'logo' => ['type'=>'image', 'label'=>'Logo (top-left)', 'default'=>''],
        'paper' => ['type'=>'color', 'label'=>'Story panel', 'default'=>'#EEF1FB'],
        'ink' => ['type'=>'color', 'label'=>'Headline and branding', 'default'=>'#202C50'],
        'accent' => ['type'=>'color', 'label'=>'Indigo accent', 'default'=>'#4C48A8'],
        'meta_color' => ['type'=>'color', 'label'=>'Date and footer text', 'default'=>'#FFFFFF'],
        'section_text' => ['type'=>'text', 'label'=>'Feature label', 'default'=>'খবরের ভেতরে'],
        'cta_text' => ['type'=>'text', 'label'=>'Footer text', 'default'=>'বিস্তারিত পড়ুন কমেন্টে'],
    ],
    'css' => '
.nsd-card{position:relative;width:1080px;height:1080px;overflow:hidden;background:{{opt.paper}};color:{{opt.ink}};font-family:"SolaimanLipi",sans-serif;text-align:left}
.nsd-card *{box-sizing:border-box}
.nsd-photo{position:absolute;top:0;right:0;bottom:114px;width:470px;background-color:#C5CFE2;background-image:url("{{image}}");background-size:cover;background-position:center}
.nsd-brand{position:absolute;left:48px;top:44px;max-width:505px;font-size:38px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
.nsd-brand img{display:block;max-width:310px;max-height:92px;width:auto;height:auto}
.nsd-card[data-logo=""] .nsd-brand img,.nsd-card:not([data-logo=""]) .nsd-brand span{display:none}
.nsd-story{position:absolute;left:48px;top:204px;width:514px;bottom:168px;display:flex;flex-direction:column;justify-content:center;gap:28px}
.nsd-label{color:{{opt.accent}};font-size:29px;line-height:1.2;border-top:6px solid {{opt.accent}};padding-top:20px;overflow-wrap:anywhere}
.nsd-label:empty{display:none}
.nsd-title{margin:0;font-size:53px;line-height:1.25;font-weight:700;overflow-wrap:anywhere}
.nsd-card[data-wc="1"] .nsd-title,.nsd-card[data-wc="2"] .nsd-title,.nsd-card[data-wc="3"] .nsd-title,.nsd-card[data-wc="4"] .nsd-title,.nsd-card[data-wc="5"] .nsd-title,.nsd-card[data-wc="6"] .nsd-title,.nsd-card[data-wc="7"] .nsd-title,.nsd-card[data-wc="8"] .nsd-title{font-size:72px}
.nsd-date{position:absolute;left:634px;right:24px;bottom:142px;padding:15px 18px;background:{{opt.accent}};color:{{opt.meta_color}};font-size:26px;line-height:1.2;text-align:center}
.nsd-footer{position:absolute;left:0;right:0;bottom:0;height:114px;padding:22px 48px;background:{{opt.accent}};color:{{opt.meta_color}};display:flex;align-items:center;justify-content:space-between;gap:24px;line-height:1.2}
.nsd-cta{font-size:28px;max-width:53%;overflow-wrap:anywhere}
.nsd-site{font-size:26px;max-width:45%;text-align:right;overflow-wrap:anywhere}
',
    'html' => '
<div class="nsd-card" data-logo="{{opt.logo}}" data-wc="{{opt.wc}}">
  <div class="nsd-photo" role="img" aria-label="{{title}}"></div>
  <div class="nsd-brand"><img src="{{opt.logo}}" alt="{{site}}" crossorigin="anonymous"><span>{{site}}</span></div>
  <div class="nsd-story"><div class="nsd-label">{{opt.section_text}}</div><div class="nsd-title" data-udnpc-text="title">{{title}}</div></div>
  <div class="nsd-date" data-udnpc-text="date">{{date}}</div>
  <div class="nsd-footer"><div class="nsd-cta" data-udnpc-text="cta">{{opt.cta_text}}</div><div class="nsd-site" data-udnpc-text="site">{{opt.domain}}</div></div>
</div>',
];
