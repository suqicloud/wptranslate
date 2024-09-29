<?php
/**
 * Plugin Name: 小半网页翻译
 * Description: 基于translate.js的WordPress在线翻译插件，支持顶部、底部、菜单和小工具位置显示，支持自定义添加翻译语言。
 * Plugin URI: https://www.jingxialai.com/4865.html
 * Version: 1.2
 * Author: Summer
 * License: GPL License
 * Author URI: https://www.jingxialai.com/
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}

// 插件激活时调用
function wp_translate_plugin_activate() {
    wp_cache_flush(); // 清除缓存
}
register_activation_hook(__FILE__, 'wp_translate_plugin_activate');

// 插件停用时调用
function wp_translate_plugin_deactivate() {
    wp_cache_flush(); // 清除缓存
}
register_deactivation_hook(__FILE__, 'wp_translate_plugin_deactivate');

// 在插件中心添加设置链接
function wp_translate_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=wp-translate-plugin">设置</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_translate_settings_link');

// 添加插件设置页面
function wp_translate_plugin_menu() {
    add_options_page(
        '小半翻译插件设置',
        '小半翻译设置',
        'manage_options',
        'wp-translate-plugin',
        'wp_translate_plugin_settings_page'
    );
}
add_action('admin_menu', 'wp_translate_plugin_menu');

// 设置页面内容
function wp_translate_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>小半翻译插件设置</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_translate_plugin_options');
            do_settings_sections('wp-translate-plugin');
            submit_button();
            ?>
            <p class="description">
                <h1>插件说明</h1>
                1、是基于translate.js实现的前端翻译，用的client.edge方式，默认会根据用户客户端ip自动显示对应语言(有设置的前提下).<br>
                2、具体的翻译语言简码到官方查看: https://translate.zvo.cn/43086.html<br>
                3、这是机翻，稳定性、翻译速度、准确性都是根据client.edge（由微软直接提供翻译支持）来的.<br>
                4、翻译语言的图标可以不填写，显示到小工具之后，还需要在小工具去添加位置.<br>
                5、默认改为本地调用translate.js文件，没有用官方的staticfile，可以自己把translate.js上传到对象存储加权限，改为远程调用.<br>
                6、<span style="color: #ff0000;">翻译按钮显示错乱或者按钮无反应，是因为和你的主题不兼容，最完美的兼容是联系你的主题作者内置.</span><br>
                7、<span style="color: #009900;">本插件介绍页面: <a target="_blank" href="https://www.jingxialai.com/4865.html">www.jingxialai.com</a></span><br>
            </p>            
        </form>
    </div>
    <?php
}

// 注册设置
function wp_translate_plugin_settings_init() {
    register_setting('wp_translate_plugin_options', 'wp_translate_plugin_position');
    register_setting('wp_translate_plugin_options', 'wp_translate_plugin_custom_languages');

    add_settings_section(
        'wp_translate_plugin_section',
        '设置翻译按钮位置',
        null,
        'wp-translate-plugin'
    );

    add_settings_field(
        'wp_translate_plugin_position',
        '翻译按钮显示位置',
        'wp_translate_plugin_position_render',
        'wp-translate-plugin',
        'wp_translate_plugin_section'
    );

    add_settings_section(
        'wp_translate_plugin_custom_languages_section',
        '自定义添加语言',
        null,
        'wp-translate-plugin'
    );

    add_settings_field(
        'wp_translate_plugin_custom_languages',
        '翻译语言设置',
        'wp_translate_plugin_custom_languages_render',
        'wp-translate-plugin',
        'wp_translate_plugin_custom_languages_section'
    );
}
add_action('admin_init', 'wp_translate_plugin_settings_init');

function wp_translate_plugin_custom_languages_render() {
    $custom_languages = get_option('wp_translate_plugin_custom_languages', []);
    ?>
    <div id="custom-languages-container">
        <?php foreach ($custom_languages as $index => $language): ?>
            <div class="custom-language-row">
                <input type="text" name="wp_translate_plugin_custom_languages[<?php echo $index; ?>][name]" value="<?php echo esc_attr($language['name']); ?>" placeholder="语言名称" />
                <input type="text" name="wp_translate_plugin_custom_languages[<?php echo $index; ?>][code]" value="<?php echo esc_attr($language['code']); ?>" placeholder="语言简码" />
                <input type="text" name="wp_translate_plugin_custom_languages[<?php echo $index; ?>][icon]" value="<?php echo esc_attr($language['icon']); ?>" placeholder="图标 URL" />
                <button type="button" class="remove-language-button">删除</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-language-button">添加语言</button>
    <script>
        document.getElementById('add-language-button').addEventListener('click', function() {
            var container = document.getElementById('custom-languages-container');
            var index = container.children.length;
            var newRow = document.createElement('div');
            newRow.classList.add('custom-language-row');
            newRow.innerHTML = '<input type="text" name="wp_translate_plugin_custom_languages[' + index + '][name]" placeholder="语言名称" />' +
                               '<input type="text" name="wp_translate_plugin_custom_languages[' + index + '][code]" placeholder="语言简码" />' +
                               '<input type="text" name="wp_translate_plugin_custom_languages[' + index + '][icon]" placeholder="图标 URL" />' +
                               '<button type="button" class="remove-language-button">删除</button>';
            container.appendChild(newRow);

            // 删除按钮
            newRow.querySelector('.remove-language-button').addEventListener('click', function() {
                container.removeChild(newRow);
            });
        });

        document.querySelectorAll('.remove-language-button').forEach(function(button) {
            button.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });
    </script>
    <?php
}


// 加载translate.js
function wp_translate_plugin_enqueue_scripts() {
    wp_enqueue_script(
        'translate-js',
        //'https://网址/3.8/translate.js', //远程调用
        plugin_dir_url(__FILE__) . 'translate.js',  //本地调用
        array(),
        null,
        true // 脚本放在页面底部加载
    );

    wp_add_inline_script(
        'translate-js',
        'document.addEventListener("DOMContentLoaded", function() {
            translate.setAutoDiscriminateLocalLanguage();
            translate.selectLanguageTag.show = false;
            translate.service.use("client.edge");
            translate.execute();
        });'
    );
}
add_action('wp_enqueue_scripts', 'wp_translate_plugin_enqueue_scripts');

// 翻译按钮位置选项
function wp_translate_plugin_position_render() {
    $options = get_option('wp_translate_plugin_position');
    ?>
    <select name="wp_translate_plugin_position">
        <option value="top" <?php selected($options, 'top'); ?>>页面顶部</option>
        <option value="bottom" <?php selected($options, 'bottom'); ?>>页面底部</option>
        <option value="menu" <?php selected($options, 'menu'); ?>>菜单中</option>
        <option value="widget" <?php selected($options, 'widget'); ?>>小工具</option>
    </select>
    <?php
}

// 在前端头部加载翻译按钮
function wp_translate_plugin_add_translate_buttons_top() {
    $position = get_option('wp_translate_plugin_position', 'top');

    // 将按钮放在顶部
    if ($position === 'top') {
        echo '<div id="translate-button-top" style="text-align: center; padding: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">';
        echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">';
        echo wp_translate_plugin_render_buttons();
        echo '</div></div>';
    }
}
add_action('wp_head', 'wp_translate_plugin_add_translate_buttons_top');

// 在前端底部加载翻译按钮
function wp_translate_plugin_add_translate_buttons_bottom() {
    $position = get_option('wp_translate_plugin_position', 'bottom');

    // 将按钮放在底部
    if ($position === 'bottom') {
        echo '<div id="translate-button-bottom" style="background-color: #f1f1f1; text-align: center; padding: 5px; z-index: 999; box-shadow: 0 -2px 5px rgba(0,0,0,0.1);">';
        echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">';
        echo wp_translate_plugin_render_buttons();
        echo '</div></div>';
    }
}
add_action('wp_footer', 'wp_translate_plugin_add_translate_buttons_bottom');

// 插入到头部或底部
function wp_translate_plugin_insert_into_header_or_footer() {
    $position = get_option('wp_translate_plugin_position', 'top');

    // 根据用户选择位置插入按钮
    if ($position === 'top') {
        add_action('wp_head', 'wp_translate_plugin_add_translate_buttons_top');
    } elseif ($position === 'bottom') {
        add_action('wp_footer', 'wp_translate_plugin_add_translate_buttons_bottom');
    }
}
add_action('init', 'wp_translate_plugin_insert_into_header_or_footer');



// 翻译语言切换按钮
function wp_translate_plugin_render_buttons() {
    $options = get_option('wp_translate_plugin_custom_languages', []);
    $buttons = '';

    foreach ($options as $language) {
        $name = esc_attr($language['name']);
        $code = esc_attr($language['code']);
        $icon = esc_url($language['icon']);
        $icon_html = $icon ? '<img src="' . $icon . '" alt="' . $name . '" style="width:20px; height:20px; border-radius:50%; vertical-align:middle; margin-right:5px;" class="ignore" />' : '';

        $buttons .= '<li style="display: flex; align-items: center; list-style: none; padding: 0; margin: 0;">
            <a href="javascript:translate.changeLanguage(\'' . $code . '\');" style="display: flex; align-items: center; padding:1px 5px; background-color:#0073aa; color:white; border-radius:5px; text-decoration: none;" class="ignore">
                ' . $icon_html . ' ' . $name . '
            </a>
        </li>';
    }

    return '<ul style="list-style:none; padding:0; margin:0; display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">' . $buttons . '</ul>';
}


// 在菜单中添加翻译按钮
function wp_translate_plugin_add_to_menu($items, $args) {
    $position = get_option('wp_translate_plugin_position', 'top');
    if ($position === 'menu') {
        $items .= '<li class="ignore"><a href="#" id="translate-menu-button">&#x1F310;Language</a></li>';
        $items .= '<div id="translate-menu-options" style="display:none; background-color:rgba(119, 119, 119, 0.8); padding:10px; border-radius:5px; position: fixed; z-index: 999; max-width: 600px; top: 50%; left: 50%; transform: translate(-50%, -50%);">';
        $items .= '<div style="display: flex; flex-wrap: wrap; justify-content: center;">';
        $items .= '<ul style="list-style:none; padding:0; margin:0; display:flex; flex-wrap:wrap; justify-content:center;">';
        $items .= wp_translate_plugin_render_buttons();
        $items .= '</ul></div></div>';

        // JavaScript弹出语言
        $items .= '
        <script>
            document.getElementById("translate-menu-button").addEventListener("click", function(e) {
                e.preventDefault();
                var options = document.getElementById("translate-menu-options");
                options.style.display = options.style.display === "none" ? "block" : "none";
            });
        </script>';
    }
    return $items;
}


add_filter('wp_nav_menu_items', 'wp_translate_plugin_add_to_menu', 10, 2);




// 小工具翻译按钮
class WP_Translate_Plugin_Widget extends WP_Widget {

    // 初始化小工具
    public function __construct() {
        parent::__construct(
            'wp_translate_plugin_widget',
            '翻译按钮小工具',
            array('description' => __('在小工具区域显示翻译按钮', 'wp_translate_plugin'))
        );
    }

    // 小工具前台显示内容
    public function widget($args, $instance) {
        $position = get_option('wp_translate_plugin_position', 'top');
        
        if ($position === 'widget') {
            echo $args['before_widget'];
            echo '<a href="#" id="translate-widget-button" class="ignore">&#x1F310;Language</a>';
            echo '<div id="translate-widget-options" style="display:none; background-color:rgba(119, 119, 119, 0.8); padding:10px; border-radius:5px; position: fixed; z-index: 999; max-width: 600px; top: 50%; left: 50%; transform: translate(-50%, -50%);">';
            echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">';
            echo wp_translate_plugin_render_buttons();
            echo '</div></div>';

            // JavaScript翻译选项
            echo '
            <script>
                document.getElementById("translate-widget-button").addEventListener("click", function(e) {
                    e.preventDefault();
                    var options = document.getElementById("translate-widget-options");
                    options.style.display = options.style.display === "none" ? "block" : "none";
                });
            </script>';
            echo $args['after_widget'];
        }
    }

    // 小工具
    public function form($instance) {
        // 小工具后台设置
    }

    // 更新小工具设置
    public function update($new_instance, $old_instance) {
        return $new_instance;
    }
}

// 注册小工具
function wp_translate_plugin_register_widget() {
    register_widget('WP_Translate_Plugin_Widget');
}
add_action('widgets_init', 'wp_translate_plugin_register_widget');

// 防止缓存
function wp_translate_plugin_prevent_cache($headers) {
    $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    $headers['Pragma'] = 'no-cache';
    $headers['Expires'] = '0';
    return $headers;
}
add_filter('wp_headers', 'wp_translate_plugin_prevent_cache');
