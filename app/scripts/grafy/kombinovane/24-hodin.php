﻿<?php

  // INIT
  require dirname(__FILE__)."/../../init.php";

  // Posledni zaznamy
  $q = MySQLi_query($GLOBALS["DBC"], "SELECT kdy, teplota, vlhkost FROM tme ORDER BY kdy DESC LIMIT 1440");

  // budeme brat kazdy 10ty zaznam, abychom se do grafu rozumne vesli
  $a = 10;

    while($t = MySQLi_fetch_assoc($q))
    {

    // budeme za tu dobu, aktualne 40 minut, pocitat prumernou teplotu,
    // abychom meli graf "uhlazenejsi" (vypada to lepe)
    $teplota = $teplota+$t['teplota'];
    $vlhkost = $vlhkost+$t['vlhkost'];
    $rosnyBod = $rosnyBod+rosnybod($t['teplota'], $t['vlhkost']);
    $count++;

      // uz mame dostatek mereni?
      if($a == 10)
      {

        // pridame teplotu do pole
        $ydata[] = round(jednotkaTeploty($teplota/$count, $u, 0), 1);
        $ydata2[] = round($vlhkost/$count, 1);
        $ydata3[] = round($rosnyBod/$count, 1);

        // pridame popisek do pole
        $labels[] = substr($t['kdy'], 11, 5);

        // "vynulujeme" teplotu
        $teplota = "";
        $vlhkost = "";
        $rosnyBod = "";
        // vynulujeme pocitadla
        $count = 0;
        $a = 0;      

      }

  // iterujeme
  $a++;

  }

  // abychom ziskali spravnou posloupnoust udaju, tak obe pole obratime
  $ydata = array_reverse($ydata);
  $ydata2 = array_reverse($ydata2);
  $ydata3 = array_reverse($ydata3);
  $labels = array_reverse($labels);

?>
<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: { renderTo: 'graf-24-hodin', zoomType: 'x', borderWidth: 1, backgroundColor: '#f7f6eb' },
            credits: { enabled: 0 },
            title: { text: '<?php echo $lang['24hodin']; ?>' },
            xAxis: { categories: ['<?php echo implode("','", $labels); ?>'], 
            labels: { rotation: -45, align: 'right', step: 4 }
            },
            yAxis: [{ 
                labels: {
                    formatter: function() { return this.value +' <?php echo "$jednotka"; ?>'; },
                    style: { color: '#c4423f' }
                },
                title: {
                    text: null,
                    style: { color: '#c4423f' }
                },
                opposite: false
            }, {
                gridLineWidth: 0,
                title: {
                    text: null,
                    style: { color: '#4572a7' }
                },
                labels: {
                    formatter: function() { return this.value +' %'; },
                    style: { color: '#4572a7' },
                max: 100
                },
                opposite: true
            }, {
                gridLineWidth: 0,
                title: {
                    text: null,
                    style: { color: '#6ba54e' }
                },
                labels: {
                    formatter: function() { return this.value +' <?php echo "$jednotka"; ?>'; },
                    style: { color: '#6ba54e' }
                },
                opposite: true
            }],
            tooltip: {
                formatter: function() {
                    var unit = {
                        '<?php echo $lang['teplota'] ?>': '<?php echo "$jednotka"; ?>',
                        '<?php echo $lang['vlhkost'] ?>': '%',
                        '<?php echo $lang['rosnybod'] ?>': '<?php echo "$jednotka"; ?>'
                    }[this.series.name];
                    return '<b>'+ this.x +'</b><br /><b>'+ this.y +' '+ unit + '</b>';
                },
                crosshairs: true,
            },
            legend: {
                layout: 'horizontal',
                align: 'left',
                x: 6,
                verticalAlign: 'top',
                y: -5,
                floating: true,
                backgroundColor: '#FFFFFF'
            },
            series: [{
                name: '<?php echo $lang['teplota'] ?>',
                type: 'spline',
                color: '#c4423f',
                yAxis: 0,
                data: [<?php echo implode(", ", $ydata); ?>],
                marker: { enabled: false }
            }, {
                name: '<?php echo $lang['vlhkost'] ?>',
                type: 'spline',
                color: '#4572a7',
                yAxis: 1,
                data: [<?php echo implode(", ", $ydata2); ?>],
                marker: { enabled: false }
            }, {
                name: '<?php echo $lang['rosnybod'] ?>',
                type: 'spline',
                color: '#6ba54e',
                yAxis: 2,
                data: [<?php echo implode(", ", $ydata3); ?>],
                marker: { enabled: false },
                visible: false
            }]
        });
    });
    
});
</script>