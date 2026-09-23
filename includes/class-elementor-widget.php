<?php
if ( ! defined( 'ABSPATH' ) ) exit;
use Elementor\Widget_Base; use Elementor\Controls_Manager; use Elementor\Group_Control_Typography;
class UDNPC_Elementor_Widget extends Widget_Base {
    public function get_name(){ return 'udnpc_photocard_button'; }
    public function get_title(){ return __( 'News PhotoCard Button', 'news-photocard-pro' ); }
    public function get_icon(){ return 'eicon-camera'; }
    public function get_categories(){ return [ 'general' ]; }
    protected function register_controls(){
        $this->start_controls_section('section_content',[ 'label'=>__('Button Content','news-photocard-pro'), 'tab'=>Controls_Manager::TAB_CONTENT ]);
        $this->add_control('button_text',[ 'label'=>__('Button Text','news-photocard-pro'), 'type'=>Controls_Manager::TEXT, 'default'=>__('📸 Download News PhotoCard (1080×1080)','news-photocard-pro') ]);
        $this->add_control('button_icon',[ 'label'=>__('Button Icon','news-photocard-pro'), 'type'=>Controls_Manager::ICONS, 'default'=>['value'=>'fas fa-camera','library'=>'fa-solid'] ]);
        $this->end_controls_section();
        $this->start_controls_section('section_style',[ 'label'=>__('Style','news-photocard-pro'), 'tab'=>Controls_Manager::TAB_STYLE ]);
        $this->add_control('text_color',[ 'label'=>__('Text Color','news-photocard-pro'), 'type'=>Controls_Manager::COLOR, 'selectors'=>['{{WRAPPER}} .udnpc-elementor-btn'=>'color: {{VALUE}} !important;'] ]);
        $this->add_control('background_color',[ 'label'=>__('Background Color','news-photocard-pro'), 'type'=>Controls_Manager::COLOR, 'selectors'=>['{{WRAPPER}} .udnpc-elementor-btn'=>'background-color: {{VALUE}} !important; border-color: {{VALUE}} !important;'] ]);
        $this->add_control('hover_background_color',[ 'label'=>__('Hover Background Color','news-photocard-pro'), 'type'=>Controls_Manager::COLOR, 'selectors'=>['{{WRAPPER}} .udnpc-elementor-btn:hover'=>'background-color: {{VALUE}} !important; border-color: {{VALUE}} !important;'] ]);
        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name'=>'typography', 'selector'=>'{{WRAPPER}} .udnpc-elementor-btn' ] );
        $this->end_controls_section();
    }
    protected function render(){
        if (!UDNPC_License::can_use_premium_features()) return;
        $s = $this->get_settings_for_display(); $text = $s['button_text'] ?: '📸 Download News PhotoCard (1080×1080)'; $post_id = get_the_ID();
        if (!$post_id || !has_post_thumbnail($post_id)){ echo '<p style="color:#777;">'.esc_html__('No featured image found for this post.','news-photocard-pro').'</p>'; return; }
        echo '<div class="udnpc-wrap" style="margin:16px 0;"><button type="button" data-post-id="'.esc_attr($post_id).'" class="udnpc-generate udnpc-elementor-btn button button-primary">';
        if (!empty($s['button_icon']['value'])){ \Elementor\Icons_Manager::render_icon($s['button_icon'], ['aria-hidden'=>'true']); echo '&nbsp;'; }
        echo esc_html($text).'</button></div>';
    }
}
