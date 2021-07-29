$(function() {
  var $demo, duration, remaining, tour;
  $demo = $("#demo");
  tour = new Tour({
    /*onStart: function() {
      return $demo.addClass("disabled", true);
    },
    onEnd: function() {
      return $demo.removeClass("disabled", true);
    },
    debug: true*/
  });
  duration = 5000;
  remaining = duration;
  tour.addSteps([
    {
      element: "#demo",
      placement: "bottom",
      title: "Welcome to Your Marketocracy Portfolio!",
      content: "This tour will guide you through the verious features of the website! <br /><br />You can also use your \"<\" &amp; \">\" Arrow Keys to navigate the tour!",
	  backdrop: true
    }, {
      element: "#escape-hatch",
      placement: "bottom",
      title: "Escape Hatch",
      content: "Click this logo to instantly return to your user dashboard from anywhere in the site.",
	  backdrop: true
    }, {
      element: "#search-bar",
      placement: "bottom",
      title: "Advanced Search Bar",
      content: "Search the entire website for such items as user profiles, stock symbols, funds, rankings, etc.",
	  backdrop: true
    }, {
      element: "#advanced-search",
      placement: "bottom",
      title: "Narrow Your Search",
      content: "Want to search only a specific area of the site? Select an option from this dropdown!",
      backdrop: true
    }, {
      element: "#tour-feedback",
      placement: "bottom",
      title: "User Feedback From",
      content: "Have a question or comment? Don't like how something looks or works? Have a suggestion for an additional feature? Have a concern or see something that just isn't working? This is the place for you!<br /><br />Fill out the form and click \"Submit Feedback\"! Simple as that!",
      backdrop: true
    }, {
      element: "#tour-quick-trade",
      placement: "bottom",
      title: "Quick Trade Window",
      content: "If at any time while using the website you want to initiate a quick trade, all you have to do is click this button and the form will become available to you!",
      backdrop: true
    }, {
      element: "#header_notification_bar",
      placement: "bottom",
      title: "User Notifications",
      content: "If anything happens on the site relating to your account, you will be notified here! This will keep track of issues such as: Corporate Actions, trade information, messages, or system updates!",
      backdrop: true
    }, {
      element: "#header_inbox_bar",
      placement: "bottom",
      title: "User Messages",
      content: "This is your personal Marketocracy inbox. Receive important update information and user to user messages!",
      backdrop: true
    }, {
      element: "#header_task_bar",
      placement: "bottom",
      title: "User Tasks",
      content: "Create and organize your own tasks!",
      backdrop: true
    }, {
      element: "#user-menu",
      placement: "bottom",
      title: "User Menu",
      content: "Have quick access to all the user level pages on the site!",
      backdrop: true
    }, {
      element: "#theme-panel",
      placement: "bottom",
      title: "Theme &amp; Layout Menu",
      content: "Change theme settings and layout options with the click of a button! You can fully customize your user experience!",
      backdrop: true
    },{
      element: "#sidebar-toggle",
      placement: "bottom",
      title: "Sidebar Toggle",
      content: "Click Here to collapse and expand the sidebar to increase the viewing area!",
      backdrop: true
    }, {
      element: "#nav-dashboard",
      placement: "right",
      title: "Navigation - Dashboard",
      content: "Quickly get back to your dashboard by clicking this link at any time.",
	  backdrop: true
    }, {
      element: "#make-trade",
      placement: "right",
      title: "Navigation - Make A Trade",
      content: "This Top-Level menu item contains everything related to trading, including: the Trade Wizard, Open Orders, and Recent Orders.",
	  backdrop: true
    }, {
      element: "#my-funds",
      placement: "right",
      title: "Navigation - My Funds",
      content: "This is your go to for all of your funds and subsequent fund pages.",
	  backdrop: true
    }, {
      element: "#nav-community",
      placement: "right",
      title: "Navigation - Community",
      content: "Here you will find community resources such as Blogs, Chats, Forums, and User Profiles.",
	  backdrop: true
    }, {
      element: "#nav-research",
      placement: "right",
      title: "Navigation - Research",
      content: "Get your stock info and weekly insight information. Also create stock watch lists.",
	  backdrop: true
    }, {
      element: "#nav-rankings",
      placement: "right",
      title: "Navigation - Rankings",
      content: "View all the Marketocracy Ranking pages.",
	  backdrop: true
    }, {
      element: "#nav-trade-school",
      placement: "right",
      title: "Navigation - Trade School",
      content: "Get information on how the site works or how to use the information provided. Get access to Investing Insights, FAQ's, Terminology, and even Tutorials!",
	  backdrop: true
    }, {
      element: "#nav-support",
      placement: "right",
      title: "Navigation - Support",
      content: "In the unfortunate situation that you have a problem, resolve it quickly with our new support features which include, support chat, support forums, and support tickets!",
	  backdrop: true
    }, {
      element: "#nav-policies",
      placement: "right",
      title: "Navigation - Policies",
      content: "View our policies and rules ranging from general use and conduct to rankings.",
	  backdrop: true
    }, {
      element: "#nav-profile",
      placement: "right",
      title: "Navigation - Profile",
      content: "Quick access link to your user profile. You can also achieve this by using the user menu at the top right of your screen.",
	  backdrop: true
    }, {
      element: "#nav-logout",
      placement: "right",
      title: "Navigation - Logout",
      content: "Quick access link to logout. You can also achieve this by using the user menu at the top right of your screen.",
	  backdrop: true
    }, {
      element: "#nav-breadcrumb",
      placement: "bottom",
      title: "Layout - Breadcrumb Navigation",
      content: "Keep track of your progress and location through the site by viewing this breadcrumb navigation bar.",
	  backdrop: true
    }, {
      placement: "top",
      title: "Layout - Resizable Window",
      content: "This site is designed to be responsive to the size of your screen. Go ahead and resize your browser window to see it in action!",
	  orphan: true
    }, {
      element: "#top-page",
      placement: "top",
      title: "Layout - Jump To Top",
      content: "On a page with a lot of content? Quickly jump from the bottom of the page to the top of the page by clicking this button.",
	  backdrop: true
    }, {
	  path: "/fund-zones.php",
      element: "#performance",
      placement: "top",
      title: "High Contrast Tables",
      content: "The tables have been designed with high contrast colors to make viewing a breeze.",
	  backdrop: true
    },{
	  path: "/fund-ledger.php",
      element: "#ledger-entries",
      placement: "top",
      title: "Advanced Sorting Tables",
      content: "Tables that have large volumes of information can become difficult to sort through. With Advanced Tables, you can sort per column, and even hide columns you choose not to view.",
	  backdrop: true
    }, {
      element: "#column-sort",
      placement: "top",
      title: "Advanced Tables - Sorting",
      content: "Simply click a column header to sort for that column."
    },{
      element: "#column-view",
      placement: "top",
      title: "Advanced Tables - Column View/Hide",
      content: "Choose which columns you would like to view by using this menu.",
	  backdrop: true 
    },{
	  path: "/drag.php",
      element: "#reorder",
      placement: "top",
      title: "Layout - Re-order Elements",
      content: "You can customize your dashboard by re-ordering these boxes. Give it a try! Just drag and drop.<br /><br /> Note: your changes will not be reflected in the demo."
    }, {
	  path: "/trade-wizard.php",
      element: "#form_wizard_1",
      placement: "top",
      title: "Step-By-Step Wizards",
      content: "Easy to use Form Wizards walk you through various processes efficiently!",
	  backdrop: true
    }, {
      element: "#form_wizard_1",
      placement: "top",
      title: "Trading Form",
      content: "This Trade Wizard is a merge of the mutliple trading wizards on the old system. There will not be any need to click any \"Recalculate\" buttons as the fields will auto calculate upon entering data.<br /><br />Enter AAPL, then click \"Continue\" for a demonstration. ",
	  backdrop: true
    }, {
      element: "#breadcrumb-drop",
      placement: "right",
      title: "Quick Navigation",
      content: "Jump from page to page within a section of the website easily with this dropdown menu.",
	  backdrop: true
    }, {
	  path: "/fund-public.php",
      element: "#profile-overview",
      placement: "top",
      title: "Public Profile",
      content: "With the roll out of the new system every member will have their very own user profile that is viewable to the public. Each user will also have their own domain. Example: jdoe.marketocracy.com",
	  backdrop: true
    }, {
      element: "#profile-sections",
      placement: "top",
      title: "Choose What The World Sees",
      content: "You will have the ability to choose what you want the world to see on your public profile.",
	  backdrop: true
    }, {
      path: "/index.php",
      placement: "top",
      title: "Thats wraps it up for now!",
      content: "We hope you enjoyed the tour.<br /><br />Please feel free to poke around and do a little bit of exploring on your own. Remember, the data provided is not accurate and is only there for demonstration purposes, so please don't make any investment decisions based on it!<br /><br />Any feedback you can provide, good or bad, will be very helpful to us. Please click on the \"Submit Feedback\" link at the top of the page and do so as often as you'd like to share your thoughts with us. We look forward to using your feedback to tailor this project to exactly what the members need and want.",
	  orphan: true,
	  backdrop: true
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
  /*$("html").smoothScroll();
  return $(".gravatar").each(function() {
    var $this, email;
    $this = $(this);
    email = md5($this.data("email"));
    return $(this).attr("src", "http://www.gravatar.com/avatar/" + email + "?s=60");
  });*/
});
