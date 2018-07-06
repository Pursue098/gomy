<!-- REQUIRED JS SCRIPTS -->

{{-- <!-- jQuery 2.1.4 -->
<script src="{{ asset('/js/jquery-2.1.1.js') }}"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset('/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ asset('/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('/js/plugins/select2/select2.full.min.js') }}"></script>

<!-- icheck -->
<script src="{{ asset('/js/plugins/iCheck/icheck.min.js') }}"></script>

<!-- switchery -->
<script src="{{ asset('/js/plugins/switchery/switchery.js') }}"></script>


<!-- Custom and plugin javascript -->
<script src="{{ asset('/js/inspinia.js') }}"></script>

<!-- Wizard -->
<script src="{{ asset('/js/plugins/staps/jquery.steps.min.js') }}"></script> --}}

<!-- Pusher -->
<script src="https://js.pusher.com/3.1/pusher.min.js"></script>

<!-- Sweet alert -->
<script src="{{ asset('/js/plugins/sweetalert/sweetalert.min.js') }}"></script>


<!-- Production JS -->
<script src="{{ mix('/js/cyrano.min.js') }}"></script>

<!-- Responsive Pagination -->
<script src="{{ asset('/js/plugins/responsive-paginate/responsive-paginate.js') }}"></script>

<!-- Wizard -->
<script src="{{ asset('/js/plugins/pace/pace.min.js') }}"></script>

<!-- Ladda -->
<script src="{{ asset('/js/plugins/ladda/spin.min.js') }}"></script>
<script src="{{ asset('/js/plugins/ladda/ladda.min.js') }}"></script>
<script src="{{ asset('/js/plugins/ladda/ladda.jquery.min.js') }}"></script>


<!-- jQuery UI -->
<!--<script src="{{ asset('/js/plugins/jquery-ui/jquery-ui.min.js') }}"></script>-->

<!-- ChartJS-->
<!--<script src="{{ asset('/js/plugins/chartJs/Chart.min.js') }}"></script>-->

<script type="text/javascript">
window.csrf_token = '{{ csrf_token() }}';

$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': csrf_token
      }
  });

   $('[data-method]').append(function(){
        return "\n"+
        "<form action='"+$(this).attr('href')+"' method='POST' style='display:none'>\n"+
        "   <input type='hidden' name='_token' value='"+csrf_token+"'>\n"+
        "   <input type='hidden' name='_method' value='"+$(this).attr('data-method')+"'>\n"+
        "</form>\n"
   })
   .removeAttr('href')
   .css('cursor','pointer')
   .attr('onclick','$(this).find("form").submit();');

  $('body').on('mousedown', '[data-method]', function(event) {
    if ($(this).data('method') == 'delete') {
      event.preventDefault();

      var form = $('form', $(this));
      var msg = $(this).data('message');

      if (! msg) {
        msg = "L'operazione non può essere annullata";
      }

      swal({
          title: "Sei sicuro?",
          text: msg,
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Si, prosegui!",
          cancelButtonText: "Annulla",
          //closeOnConfirm: true,
      }).then(function() {
        form.submit();
      }, function(dismiss) {
        // nothing
      });
    }
  });

   // Bind normal buttons
  $( '.ladda-button' ).ladda( 'bind', { timeout: 2000 } );

  // responsive pagination
  $('.pagination').rPage();

  var alerts_clicked = false;

  $('#btn-alerts').click(function() {
    if (! alerts_clicked) {
      alerts_clicked = true;

      $.post('{{ route('notifications.mark_as_read') }}', {'time': $(this).data('time')});
    }
  });
});
</script>