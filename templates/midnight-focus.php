<?php
return [
    'key' => 'midnight-focus',
    'name' => 'Midnight Focus',
    'fields' => [
        'logo' => ['type'=>'image', 'label'=>'Logo (top-left)', 'default'=>''],
        'base' => ['type'=>'color', 'label'=>'Midnight background', 'default'=>'#0A1424'],
        'title_color' => ['type'=>'color', 'label'=>'Headline and branding', 'default'=>'#FFFFFF'],
        'accent' => ['type'=>'color', 'label'=>'Highlight accent', 'default'=>'#BFE86A'],
        'meta_color' => ['type'=>'color', 'label'=>'Date and website', 'default'=>'#D3DCE6'],
        'section_text' => ['type'=>'text', 'label'=>'Story label', 'default'=>'আজকের আলোচিত'],
        'cta_text' => ['type'=>'text', 'label'=>'Footer text', 'default'=>'পুরো খবর কমেন্টে'],
    ],
    'css' => '
.nmf-card{position:relative;width:1080px;height:1080px;overflow:hidden;background:{{opt.base}};color:{{opt.title_color}};font-family:"SolaimanLipi",sans-serif;text-align:left}
.nmf-card *{box-sizing:border-box}
.nmf-photo{position:absolute;inset:0;background-image:url("{{image}}");background-size:cover;background-position:center}
.nmf-shade{position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,20,36,.75) 0%,rgba(10,20,36,.05) 30%,rgba(10,20,36,.70) 49%,{{opt.base}} 76%)}
.nmf-header{position:absolute;top:44px;left:52px;right:52px;display:flex;align-items:center;justify-content:space-between;gap:28px}
.nmf-brand{max-width:54%;font-size:36px;line-height:1.2;font-weight:700;overflow-wrap:anywhere}
.nmf-brand img{display:block;max-width:300px;max-height:80px;width:auto;height:auto;background:#fff;padding:10px 16px;border-radius:4px}
.nmf-card[data-logo=""] .nmf-brand img,.nmf-card:not([data-logo=""]) .nmf-brand span{display:none}
.nmf-date{max-width:44%;font-size:26px;color:{{opt.meta_color}};line-height:1.2;text-align:right}
.nmf-story{position:absolute;left:56px;right:56px;top:540px;bottom:136px;display:flex;flex-direction:column;justify-content:center;gap:24px}
.nmf-label{color:{{opt.accent}};font-size:29px;line-height:1.2;border-left:5px solid {{opt.accent}};padding-left:16px;overflow-wrap:anywhere}
.nmf-label:empty{display:none}
.nmf-title{margin:0;font-size:62px;line-height:1.22;font-weight:700;overflow-wrap:anywhere}
.nmf-card[data-wc="1"] .nmf-title,.nmf-card[data-wc="2"] .nmf-title,.nmf-card[data-wc="3"] .nmf-title,.nmf-card[data-wc="4"] .nmf-title,.nmf-card[data-wc="5"] .nmf-title,.nmf-card[data-wc="6"] .nmf-title,.nmf-card[data-wc="7"] .nmf-title,.nmf-card[data-wc="8"] .nmf-title{font-size:80px}
.nmf-footer{position:absolute;left:56px;right:56px;bottom:38px;border-top:1px solid {{opt.meta_color}};padding-top:25px;display:flex;align-items:center;justify-content:space-between;gap:24px;line-height:1.2}
.nmf-cta{font-size:27px;color:{{opt.accent}};max-width:53%;overflow-wrap:anywhere}
.nmf-site{font-size:26px;color:{{opt.meta_color}};max-width:45%;text-align:right;overflow-wrap:anywhere}
',
    'html' => '
<div class="nmf-card" data-logo="{{opt.logo}}" data-wc="{{opt.wc}}">
  <div class="nmf-photo" role="img" aria-label="{{title}}"></div><div class="nmf-shade"></div>
  <div class="nmf-header"><div class="nmf-brand"><img src="{{opt.logo}}" alt="{{site}}" crossorigin="anonymous"><span>{{site}}</span></div><div class="nmf-date" data-udnpc-text="date">{{date}}</div></div>
  <div class="nmf-story"><div class="nmf-label">{{opt.section_text}}</div><div class="nmf-title" data-udnpc-text="title">{{title}}</div></div>
  <div class="nmf-footer"><div class="nmf-cta" data-udnpc-text="cta">{{opt.cta_text}}</div><div class="nmf-site" data-udnpc-text="site">{{opt.domain}}</div></div>
</div>',
];
