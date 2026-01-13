<?php
/**
 * Hero section template part
 */
?>
<section class="w-full bg-white dark:bg-[#2a1b24] py-6">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-auto lg:h-[400px]">
            <!-- Main Banner -->
            <div class="lg:col-span-2 relative w-full h-64 lg:h-full rounded-2xl overflow-hidden group shadow-lg">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105"
                    style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCO3ujvmBKtV54juslgCSNOxkMe29Sm4KBtkaSpyiRUlASTUUIbcohHwobSDIVohJBu8AmnPuTv3MSL6Gd1lm83bu9dOx481BRWVwR8XIxL19tOKL--2u4Pe3sj0Z0W-OkXPXMcGG6qWtKGBufWK-ZbodyltyAScGIQ_fLviIFsxwu6nSbwtfC2-P40sBtArCJVBC41KxrlOEUOi5mOjxOTbqlzUMkXgB_twPFRSfTjQ_IiJTmxboZkExGe6UK17rZ-9dY9xa02QwA')">
                </div>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col justify-center px-8 lg:px-12">
                    <span class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full w-fit mb-4">HOT
                        DEAL</span>
                    <h2 class="text-3xl lg:text-5xl font-bold text-white mb-2 leading-tight">Siêu Sale Hè <br />Giảm tới
                        50%</h2>
                    <p class="text-gray-200 text-lg mb-6 max-w-md">Săn deal mỹ phẩm chính hãng cực hot ngay hôm nay. Số
                        lượng có hạn.</p>
                    <a href="<?php echo home_url('/sale'); ?>"
                        class="bg-primary hover:bg-primary/90 text-white font-bold py-3 px-8 rounded-lg w-fit transition-all flex items-center gap-2">
                        Mua Ngay <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Side Banners -->
            <div class="hidden lg:flex flex-col gap-6 h-full">
                <div class="flex-1 relative rounded-2xl overflow-hidden group shadow-md">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105"
                        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAeln3lkrATuPXejj-L3ac_EnmPotqxPRVjA19PuhU9i9W7_xDgeyHQ3p_AztYYF00FDHjs_eLM-alNxtlladV3FPBGBoES25VQCCHUDZpKh0xjGmagCaQPWgV66grp9Xe6Pri909519YzoDL7HborehhU6PdoQ1PixmfBn5mQbhsHartS4ESw0vEX5NVSFI1C-d5xeD6psfDdZ3aozR-qUicncxtAvTQi9IVs6bKCsDus1-INMtNbbg77gnegx0OXaD3D-_ZPZoIw')">
                    </div>
                    <div
                        class="absolute inset-0 bg-black/30 hover:bg-black/20 transition-colors p-6 flex flex-col justify-end">
                        <h3 class="text-white text-xl font-bold">Son Môi Cao Cấp</h3>
                        <p class="text-gray-200 text-sm">Mua 1 tặng 1</p>
                    </div>
                </div>
                <div class="flex-1 relative rounded-2xl overflow-hidden group shadow-md">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105"
                        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAaeDlcXSmU1ecwoG6dew6woFJWgzWvBPdE-ouH-Q07FtH9p3ZYQKPnAPbyiMk1Ii2A7EI99cJRjqPysu6TiklqjDatEPt2ESHRdAqPlRZdU6Qb_d7KYReaPsRvw7J6TqkS46djwEJe_FYBAYLj4ZJMgvs323Ei6t8onBEurha_GD8nudrUdVtUFphOMPvZwfM9ouyX-sj9CkxmjKmk41x4HIf0gNWx3KvkbL1zC5X9JOdT38HU_cgVXnUml9wtHTKpv18FxwGapmE')">
                    </div>
                    <div
                        class="absolute inset-0 bg-black/30 hover:bg-black/20 transition-colors p-6 flex flex-col justify-end">
                        <h3 class="text-white text-xl font-bold">Chống Nắng Đỉnh Cao</h3>
                        <p class="text-gray-200 text-sm">Bảo vệ da toàn diện</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>