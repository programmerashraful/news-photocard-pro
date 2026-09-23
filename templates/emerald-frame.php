<?php
return [
    'key' => 'emerald-frame',
    'name' => 'Emerald Frame',
    'fields' => [
        'logo' => ['type'=>'image', 'label'=>'Logo (top-left)', 'default'=>''],
        'base' => ['type'=>'color', 'label'=>'Emerald frame', 'default'=>'#103F38'],
        'paper' => ['type'=>'color', 'label'=>'Headline panel', 'default'=>'#F4F3E9'],
        'ink' => ['type'=>'color', 'label'=>'Headline color', 'default'=>'#123D35'],
        'accent' => ['type'=>'color', 'label'=>'Lime accent', 'default'=>'#D6E893'],
        'meta_color' => ['type'=>'color', 'label'=>'Date and website', 'default'=>'#FFFFFF'],
        'section_text' => ['type'=>'text', 'label'=>'Story label', 'default'=>'প্রকৃতি ও জীবন'],
        'cta_text' => ['type'=>'text', 'label'=>'Footer text', 'default'=>'আরও জানুন কমেন্টে'],
    ],
    'css' => '
.nef-card{position:relative;width:1080px;height:1080px;overflow:hidden;background:{{opt.base}};color:{{opt.ink}};font-family:"SolaimanLipi",sans-serif;text-align:left}
.nef-card *{box-sizing:border-box}
.nef-header{position:absolute;top:30px;left:48px;right:48px;height:86px;display:flex;align-items:center;justify-content:space-between;gap:26px;color:{{opt.meta_color}}}
.nef-brand{max-width:55%;font-size:36px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
.nef-brand img{display:block;max-width:300px;max-height:78px;width:auto;height:auto;background:#fff;padding:8px 14px;border-radius:4px}
.nef-card[data-logo=""] .nef-brand img,.nef-card:not([data-logo=""]) .nef-brand span{display:none}
.nef-date{max-width:43%;font-size:25px;line-height:1.2;text-align:right}
.nef-photo{position:absolute;top:140px;left:40px;right:40px;height:504px;border-radius:160px 12px 0 0;border:3px solid {{opt.accent}};background-color:#B9CEC0;background-image:url("{{image}}");background-size:cover;background-position:center}
.nef-story{position:absolute;left:76px;right:76px;top:604px;bottom:116px;padding:28px 36px;background:{{opt.paper}};border-top:7px solid {{opt.accent}};display:flex;flex-direction:column;justify-content:center;gap:18px}
.nef-label{color:{{opt.ink}};font-size:27px;line-height:1.2;overflow-wrap:anywhere}
.nef-label:empty{display:none}
.nef-title{margin:0;font-size:57px;line-height:1.22;font-weight:700;overflow-wrap:anywhere}
.nef-card[data-wc="1"] .nef-title,.nef-card[data-wc="2"] .nef-title,.nef-card[data-wc="3"] .nef-title,.nef-card[data-wc="4"] .nef-title,.nef-card[data-wc="5"] .nef-title,.nef-card[data-wc="6"] .nef-title,.nef-card[data-wc="7"] .nef-title,.nef-card[data-wc="8"] .nef-title{font-size:72px}
.nef-footer{position:absolute;bottom:32px;left:48px;right:48px;min-height:52px;display:flex;align-items:center;justify-content:space-between;gap:24px;line-height:1.2}
.nef-cta{font-size:27px;color:{{opt.accent}};max-width:53%;overflow-wrap:anywhere}
.nef-site{font-size:25px;color:{{opt.meta_color}};max-width:45%;text-align:right;overflow-wrap:anywhere}
',
    'html' => '
<div class="nef-card" data-logo="{{opt.logo}}" data-wc="{{opt.wc}}">
  <div class="nef-header"><div class="nef-brand"><img src="{{opt.logo}}" alt="{{site}}" crossorigin="anonymous"><span>{{site}}</span></div><div class="nef-date" data-udnpc-text="date">{{date}}</div></div>
  <div class="nef-photo" role="img" aria-label="{{title}}"></div>
  <div class="nef-story"><div class="nef-label">{{opt.section_text}}</div><div class="nef-title" data-udnpc-text="title">{{title}}</div></div>
  <div class="nef-footer"><div class="nef-cta" data-udnpc-text="cta">{{opt.cta_text}}</div><div class="nef-site" data-udnpc-text="site">{{opt.domain}}</div></div>
</div>',
];
