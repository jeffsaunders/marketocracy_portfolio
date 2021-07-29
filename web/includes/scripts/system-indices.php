	<!--START MARKET STATUS-->

	<script>
	$(document).ready(mktstat = function() {
		$.ajax({
			url: '/includes/portlets/ajax/market-status-ajax.php',
			success: function(data) {
				$('#market-status').html(data);
			}
		});
	});
	var refreshInterval = setInterval(mktstat, <?php echo $_SESSION['market_status_refresh'];?>);
	</script>

	<!--END MARKET STATUS-->

	<!--START INDEX FEEDS-->

     <script>
	 // Mobile version (small screen)
	$(document).ready(mobile = function() {
		$.ajax({
		  url: '/includes/portlets/ajax/indices-mobile-ajax.php',
		  success: function(data) {
			$('#details-mobile').html(data);
		  }

		});
	});
	var refreshInterval = setInterval(mobile, <?php echo $_SESSION['indices_refresh'];?>);
	</script>

     <script>
	$(document).ready(sp500 = function() {
		$.ajax({
//		  url: '/includes/portlets/ajax/indices-ajax-sp500.php',
		  url: '/includes/portlets/ajax/indices-update-ajax.php?index=sp500&label=S%26P%20500&symbol=INX',
		  success: function(data) {
			$('#details-sp500').html(data);
		  }

		});
	});
	var refreshInterval = setInterval(sp500, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
    <script>
	$(document).ready(nasdaq = function() {
			$.ajax({
//			  url: '/includes/portlets/ajax/indices-ajax-nasdaq.php',
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=nasdaq&label=NASDAQ&symbol=IXIC',
			  success: function(data) {
				$('#details-nasdaq').html(data);
			  }
			});
	});
	
	var refreshInterval = setInterval(nasdaq, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
    <script>
	$(document).ready(djia = function() {
			$.ajax({
//			  url: '/includes/portlets/ajax/indices-ajax-djia.php',
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=djia&label=Dow%20Jones&symbol=DJI',
			  success: function(data) {
				$('#details-djia').html(data);
			  }
			});
	});

	var refreshInterval = setInterval(djia, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
    <script>
	$(document).ready(nyse = function() {
			$.ajax({
//			  url: '/includes/portlets/ajax/indices-ajax-nyse.php',
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=nyse&label=NYSE&symbol=NYA',
			  success: function(data) {
				$('#details-nyse').html(data);
			  }
			});
	});

	var refreshInterval = setInterval(nyse, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
    <script>
	$(document).ready(rut = function() {
			$.ajax({
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=rut&label=Russell%202000&symbol=RUT',
			  success: function(data) {
				$('#details-rut').html(data);
			  }
			});
	});

	var refreshInterval = setInterval(rut, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
<!--
    <script>
	$(document).ready(w5000 = function() {
			$.ajax({
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=w5000&label=Wilshire%205000&symbol=W5000',
			  success: function(data) {
				$('#details-w5000').html(data);
			  }
			});
	});

	var refreshInterval = setInterval(w5000, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
-->
    <script>
	$(document).ready(rua = function() {
			$.ajax({
			  url: '/includes/portlets/ajax/indices-update-ajax.php?index=rua&label=Russell%203000&symbol=RUA',
			  success: function(data) {
				$('#details-rua').html(data);
			  }
			});
	});

	var refreshInterval = setInterval(rua, <?php echo $_SESSION['indices_refresh'];?>);
	</script>
    <!--END INDEX FEEDS-->

