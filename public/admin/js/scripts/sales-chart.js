// Dashboard - eCommerce
//----------------------
(function(window, document, $) {

   // Chart shadow LineAlt
   Chart.defaults.LineAlt = Chart.defaults.line;
   var draw = Chart.controllers.line.prototype.draw;
   var custom = Chart.controllers.line.extend({
      draw: function() {
         draw.apply(this, arguments);
         var ctx = this.chart.chart.ctx;
         var _stroke = ctx.stroke;
         ctx.stroke = function() {
            ctx.save();
            ctx.shadowColor = "rgba(156, 46, 157,0.5)";
            ctx.shadowBlur = 20;
            ctx.shadowOffsetX = 2;
            ctx.shadowOffsetY = 20;
            _stroke.apply(this, arguments);
            ctx.restore();
         };
      }
   });
   Chart.controllers.LineAlt = custom;

})(window, document, jQuery);
