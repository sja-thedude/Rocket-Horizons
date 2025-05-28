<?php
require_once '../includes/header.php';
?>

<div class="con"><h2><?php echo t('title-home');?></h2></div>
<p><?php echo t('description-home');?></p>

<div class="hero-slider">
    <div class="slide active">
        <img src="slider_images/1.jpg" alt="<?php echo t('hero_slide1'); ?>">
        <div class="caption"><?php echo t('hero_slide1'); ?></div>
    </div>
    <div class="slide">
        <img src="slider_images/2.jpg" alt="<?php echo t('hero_slide2'); ?>">
        <div class="caption"><?php echo t('hero_slide2'); ?></div>
    </div>
    <div class="slide">
        <img src="slider_images/3.jpg" alt="<?php echo t('hero_slide3'); ?>">
        <div class="caption"><?php echo t('hero_slide3'); ?></div>
    </div>
</div>


<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
function showSlide(index) {
    slides.forEach(s => s.classList.remove('active'));
    slides[index].classList.add('active');
}
setInterval(() => {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}, 3000);
</script>

<?php
require_once '../includes/footer.php';
?>
