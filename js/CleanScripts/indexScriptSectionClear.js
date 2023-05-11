
$(document).ready(function(){
  $(this).scrollTop(0);
});

window.onload = function(){
  if($('.toast__container').css('opacity') === '1'){
    setTimeout(function() {
      $('.toast__container').fadeOut("slow", function() { $(this).remove(); } );
    }, 5000);
  }
};




// Open and Close Modal Functions
modaln1 = document.getElementsByClassName('modal')[0];
modaln2 = document.getElementsByClassName('modal2')[0];

$('.modal-toggle').click(function(){
  modaln1.scrollIntoView();
  $('.modal').toggleClass('modal-show');
  $('.content').toggleClass('content-blurred');
  $('body').toggleClass('no-scroll');
});

$('.modal2-toggle').click(function(){
  modaln2.scrollIntoView();
  $('.modal2').toggleClass('modal2-show');
  $('.content').toggleClass('content-blurred');
  $('body').toggleClass('no-scroll');
});

// Esc Modal Close Function
$(document).keyup(function(e) {
  if (e.key === "Escape") {
    $('.modal').removeClass('modal-show');
    $('.content').removeClass('content-blurred');
    $('body').removeClass('no-scroll');
  }
});

$(document).keyup(function(e) {
  if (e.key === "Escape") {
    $('.modal2').removeClass('modal2-show');
    $('.content').removeClass('content-blurred');
    $('body').removeClass('no-scroll');
  }
});

$(function(){

  // Zero Slider
  $('.zero-slider').flickity({
    pauseAutoPlayOnHover: false,
    prevNextButtons: false,
    cellAlign: 'center',
    freeScroll: false,
    wrapAround: false,
    draggable: false,
    pageDots: false,
    autoPlay: 3000,
    fade: true,
  });


  // Feather Replacement
  feather.replace()

  // Smooth Scroll Reveal
  ScrollReveal().reveal('.head-section, .title, .screen, .features, .cards, .zero-slider', {
    distance: '40px',
    duration: 2000,
    mobile: false,
    reset: false,
    opacity: 0
  });

});

//ParamHandlers
$(document).ready(function(){
  const params = new URLSearchParams(window.location.search);
  if(params.get('success') === 'false') {
    modaln2.scrollIntoView();
    $('.modal2').toggleClass('modal2-show');
    $('.content').toggleClass('content-blurred');
    $('body').toggleClass('no-scroll');
    if (params.get('escape') === 'handle') {
      $('#escape-error').innerText("Please, introduce only valid names")
    }
  }
});
//Toast Alert on Form Submission
$(document).ready(function(){
  const params = new URLSearchParams(window.location.search);
  if (params.get('success') === 'true') {
    $('.toast__container').css('opacity', 1);
  }

});




$(document).ready(function(){
  $('.toast__close').click(function(e){
    e.preventDefault();
    var parent = $(this).parent('.toast');
    parent.fadeOut("slow", function() { $(this).remove(); } );
  });
});