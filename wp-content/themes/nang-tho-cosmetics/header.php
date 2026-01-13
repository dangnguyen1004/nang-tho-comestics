<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <!-- Tailwind CSS (Dev Mode) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        "primary": "#ee2b8c",
                        "background-light": "#f8f6f7",
                        "background-dark": "#221019",
                        "text-dark": "#1b0d14",
                    },
                    fontFamily: {
                        "sans": ["Manrope", "sans-serif"],
                    }
                }
            }
        }
    </script>
    <?php wp_head(); ?>
</head>

<body <?php body_class('font-sans text-gray-900 bg-[#fdf8f6]'); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site min-h-screen flex flex-col">

        <header
            class="sticky top-0 z-50 w-full bg-white dark:bg-[#2a1b24] border-b border-[#f3e7ed] dark:border-white/10 shadow-sm">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20 gap-4">
                    <!-- Logo -->
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="flex items-center gap-2 shrink-0 cursor-pointer">
                        <h1 class="text-xl md:text-2xl font-bold tracking-tight text-text-dark dark:text-white">
                            Nàng Thơ <span class="text-primary">Cosmetics</span></h1>
                    </a>

                    <!-- Search Bar (Desktop) -->
                    <div class="hidden md:flex flex-1 max-w-xl mx-8">
                        <form role="search" method="get" class="relative w-full"
                            action="<?php echo esc_url(wc_get_page_permalink('shop') ?: home_url('/shop/')); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400">search</span>
                            </div>
                            <input type="search" name="s"
                                class="block w-full pl-10 pr-3 py-2.5 border border-transparent rounded-lg leading-5 bg-background-light dark:bg-white/5 text-text-dark dark:text-white placeholder-gray-400 focus:outline-none focus:bg-white dark:focus:bg-white/10 focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition duration-150 ease-in-out"
                                placeholder="<?php echo esc_attr_x('Tìm kiếm sản phẩm, thương hiệu...', 'placeholder', 'nang-tho-cosmetics'); ?>"
                                value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" />
                            <button type="submit"
                                class="absolute inset-y-0 right-0 px-4 py-1 m-1 rounded-md bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                                Tìm kiếm
                            </button>
                        </form>
                    </div>

                    <!-- Icons & User Actions -->
                    <div class="flex items-center gap-4">
                        <a href="<?php echo wc_get_cart_url(); ?>"
                            class="p-2 text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors relative">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            <?php if (function_exists('WC') && WC()->cart->get_cart_contents_count() > 0): ?>
                                <span
                                    class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-primary rounded-full">
                                    <?php echo WC()->cart->get_cart_contents_count(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>"
                            class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-background-light dark:hover:bg-white/5 transition-colors text-sm font-bold text-text-dark dark:text-white">
                            <span class="material-symbols-outlined">account_circle</span>
                            <span>
                                <?php is_user_logged_in() ? _e('Tài khoản', 'nang-tho-cosmetics') : _e('Đăng nhập', 'nang-tho-cosmetics'); ?>
                            </span>
                        </a>
                        <button id="mobile-menu-toggle" class="md:hidden p-2 text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors" aria-label="Toggle menu">
                            <span class="material-symbols-outlined" id="menu-icon">menu</span>
                            <span class="material-symbols-outlined hidden" id="close-icon">close</span>
                        </button>
                    </div>
                </div>

                <!-- Navigation (Desktop) -->
                <?php
                if (has_nav_menu('primary')) {
                    ?>
                    <nav class="hidden md:flex items-center gap-8 pb-3 text-sm font-medium text-text-dark dark:text-gray-200">
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'primary',
                                'container' => false,
                                'menu_class' => 'flex items-center gap-8',
                                'walker' => new Nang_Tho_Nav_Walker(),
                                'fallback_cb' => false,
                            )
                        );
                        ?>
                    </nav>
                    <?php
                } else {
                    // Fallback: Display default menu items if menu is not set in admin
                    ?>
                    <nav class="hidden md:flex items-center gap-8 pb-3 text-sm font-medium text-text-dark dark:text-gray-200">
                        <a href="<?php echo esc_url(home_url('/')); ?>"
                            class="hover:text-primary transition-colors border-b-2 border-primary">Trang chủ</a>
                        <a href="<?php echo esc_url(home_url('/shop')); ?>"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50">Chăm
                            sóc da</a>
                        <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=makeup"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50">Trang
                            điểm</a>
                        <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=body"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50">Cơ
                            thể & Tóc</a>
                        <a href="<?php echo esc_url(home_url('/brands')); ?>"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50">Thương
                            hiệu</a>
                        <a href="<?php echo esc_url(home_url('/sale')); ?>"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50 text-primary font-bold">Khuyến
                            mãi</a>
                        <a href="<?php echo esc_url(home_url('/blog')); ?>"
                            class="hover:text-primary transition-colors border-b-2 border-transparent hover:border-primary/50">Blog</a>
                    </nav>
                    <?php
                }
                ?>

                <!-- Mobile Navigation -->
                <div id="mobile-menu" class="hidden md:hidden border-t border-[#f3e7ed] dark:border-white/10">
                    <?php
                    if (has_nav_menu('primary')) {
                        ?>
                        <nav class="py-4">
                            <?php
                            wp_nav_menu(
                                array(
                                    'theme_location' => 'primary',
                                    'container' => false,
                                    'menu_class' => 'flex flex-col space-y-2',
                                    'walker' => new Nang_Tho_Nav_Walker_Mobile(),
                                    'fallback_cb' => false,
                                )
                            );
                            ?>
                        </nav>
                        <?php
                    } else {
                        // Fallback: Display default menu items if menu is not set in admin
                        ?>
                        <nav class="py-4 flex flex-col space-y-2">
                            <a href="<?php echo esc_url(home_url('/')); ?>"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Trang chủ</a>
                            <a href="<?php echo esc_url(home_url('/shop')); ?>"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Chăm sóc da</a>
                            <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=makeup"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Trang điểm</a>
                            <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=body"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Cơ thể & Tóc</a>
                            <a href="<?php echo esc_url(home_url('/brands')); ?>"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Thương hiệu</a>
                            <a href="<?php echo esc_url(home_url('/sale')); ?>"
                                class="px-4 py-2 text-sm font-medium text-primary font-bold hover:bg-background-light dark:hover:bg-white/5 transition-colors">Khuyến mãi</a>
                            <a href="<?php echo esc_url(home_url('/blog')); ?>"
                                class="px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors">Blog</a>
                        </nav>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </header>

        <script>
            // Mobile Menu Toggle
            document.addEventListener('DOMContentLoaded', function() {
                const toggleButton = document.getElementById('mobile-menu-toggle');
                const mobileMenu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('menu-icon');
                const closeIcon = document.getElementById('close-icon');

                if (toggleButton && mobileMenu) {
                    toggleButton.addEventListener('click', function() {
                        mobileMenu.classList.toggle('hidden');
                        menuIcon.classList.toggle('hidden');
                        closeIcon.classList.toggle('hidden');
                    });

                    // Close menu when clicking outside
                    document.addEventListener('click', function(event) {
                        const isClickInside = mobileMenu.contains(event.target) || toggleButton.contains(event.target);
                        if (!isClickInside && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                            menuIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                        }
                    });

                    // Close menu when clicking on a link
                    const mobileLinks = mobileMenu.querySelectorAll('a');
                    mobileLinks.forEach(function(link) {
                        link.addEventListener('click', function() {
                            mobileMenu.classList.add('hidden');
                            menuIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                        });
                    });
                }
            });
        </script>