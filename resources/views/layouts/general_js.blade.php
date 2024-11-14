
<!-- BEGIN VENDOR JS-->
<script src="{{asset('admin/js/vendors.min.js')}}"></script>
<script src="{{ asset('admin/vendors/toastr/toastr.min.js') }}"></script>
<script src="{{asset('admin/vendors/sweetalert/sweetalert.min.js')}}"></script>

<script src="{{asset('admin/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/js/dataTables.select.min.js')}}"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


<!-- BEGIN VENDOR JS-->

<!-- BEGIN PAGE VENDOR JS-->
@yield('vendor-script')
<!-- END PAGE VENDOR JS-->

<!-- BEGIN THEME  JS-->
<script src="{{ asset('admin/js/plugins.js') }}"></script>
<script src="{{ asset('admin/js/search.js') }}"></script>
<script src="{{ asset('admin/js/search.js') }}"></script>
<script src="{{asset('admin/vendors/select2/select2.full.min.js')}}"></script>
<!-- END THEME  JS-->

<!-- BEGIN PAGE LEVEL JS-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<!-- END PAGE LEVEL JS-->

<!-- BEGIN PAGE scripts -->
@yield('page-scripts')
<!-- END PAGE scripts-->
    
<script>
    
    $.ajaxSetup({headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")}});

    //initialize all modals
    $('.modal').modal({
        dismissible: true
    });

    $("body").on("submit", ".ajax-submit", function (e) {
        e.preventDefault();         
    });
    
    // spinner version without timeout
    // $('.submit-form').on('click', function () {
    //     var $this = $(this);
    //     $this.data("ohtml", $this.html());
    //     var nhtml = "<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> Loading ...";
    //     $this.html(nhtml);
    //     $this.attr("disabled", true);
    // });

    $(document).ready(function(){
        $('.navbar-list.billbae-list>li>a').click(function() {
            $('.navbar-list>li>a').removeClass('active');
            $(this).addClass('active');
            event.stopPropagation();
        });
        $('.tooltipped').tooltip()
        $(".card-alert .close").click(function(){$(this).closest(".card-alert").fadeOut("slow")});


        $('.tooltipped').tooltip();



    });

    $(document).click(function() {
        $('.navbar-list.billbae-list>li>a').removeClass('active');
    });
    $(document).ready(function() {
    // Toggle submenu on click of the down arrow icon
    $('.toggle-submenu').click(function() {
        $(this).next('.collapsible-sub').toggle();
    });
});
</script>
