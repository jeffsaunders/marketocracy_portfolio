$(function() {
  var $demo, duration, remaining, tour;
  $demo = $("#demo");
  tour = new Tour({
    onStart: function() {
      return $demo.addClass("disabled", true);
    },
    onEnd: function() {
      return $demo.removeClass("disabled", true);
    },
    debug: true
  });
  duration = 5000;
  remaining = duration;
  tour.addSteps([
    {
      element: "#demo",
      placement: "bottom",
      title: "Welcome to Bootstrap Tour!",
      content: "Introduce new users to your product by walking them through it step by step.\nBuilt on the awesome\n<a href='http://twitter.github.com/bootstrap' target='_blank'>Bootstrap from Twitter.</a>"
    }, {
      element: "#usage",
      placement: "top",
      title: "A super simple setup",
      content: "Easy is better, right? Easy like Bootstrap. The tour is up and running with just a      few options and steps."
    }, {
      element: "#options",
      placement: "top",
      title: "Flexibilty and expressiveness",
      content: "There are more options for those who want to get on the dark side.<br>\nPower to the people!",
      reflex: true
    }, {
      element: "#duration",
      placement: "top",
      title: "Automagically expiring step",
      content: "A new addition: make your tour (or step) completely automatic. You set the duration, Bootstrap\nTour does the rest. For instance, this step will disappear in <em>5</em> seconds.",
      duration: 5000
    }, {
      element: "#methods",
      placement: "top",
      title: "A new shiny Backdrop option",
      content: "If you need to highlight the current step's element, activate the backdrop and you won't lose\nthe focus anymore!",
      backdrop: true
    }, {
      title: "And support for orphan steps",
      content: "If you activate the orphan property, the step(s) are shown centered in the page, and you can\nforget to specify element and placement!",
      orphan: true
    }, {
      path: "/index-test.php",
      element: "#reflex",
      placement: "bottom",
      title: "Reflex mode",
      content: "Reflex mode is enabled, click on the page heading to continue!",
      reflex: true
    }, {
      path: "/page.html",
      element: "h1",
      placement: "bottom",
      title: "See, you are not restricted to only one page",
      content: "Well, nothing to see here. Click next to go back to the index page."
    }, {
      path: "/",
      element: "#license",
      placement: "top",
      title: "Best of all, it's free!",
      content: "Yeah! Free as in beer... or speech. Use and abuse, but don't forget to contribute!"
    }, {
      element: ".navbar-nav > li:last",
      placement: "bottom",
      title: "Fixed position",
      content: "Works well for fixed positioned elements! :)"
    }
  ]);
  tour.init();
  tour.start();
  if (tour.ended()) {
    $('<div class="alert alert-info alert-dismissable"><button class="close" data-dismiss="alert" aria-hidden="true">&times;</button>You ended the demo tour. <a href="#" data-demo>Restart the demo tour.</a></div>').prependTo(".content").alert();
  }
  $(document).on("click", "[data-demo]", function(e) {
    e.preventDefault();
    if ($(this).hasClass("disabled")) {
      return;
    }
    tour.restart();
    return $(".alert").alert("close");
  });
  $("html").smoothScroll();
  return $(".gravatar").each(function() {
    var $this, email;
    $this = $(this);
    email = md5($this.data("email"));
    return $(this).attr("src", "http://www.gravatar.com/avatar/" + email + "?s=60");
  });
});
