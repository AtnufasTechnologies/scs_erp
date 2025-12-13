</div>
<!-- end page content-->
</div>
<!--end page content wrapper-->
</div>
<!--end wrapper-->


<!-- JS Files-->
<script src="{{asset('admin/js/jquery.min.js')}}"></script>
<script src="{{asset('admin/plugins/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{asset('admin/plugins/metismenu/js/metisMenu.min.js')}}"></script>
<script src="{{asset('admin/js/bootstrap.bundle.min.js')}}"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>

<!--plugins-->
<script src="{{asset('admin/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
<script src="{{asset('admin/plugins/apexcharts-bundle/js/apexcharts.min.js')}}"></script>
<!-- <script src="{{asset('admin/plugins/chartjs/chart.min.js')}}"></script> -->
<script src="{{asset('admin/js/index.js')}}"></script>
<!-- <script src="{{asset('admin/plugins/chartjs/custom-script.js')}}"></script> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Main JS-->
<script src="{{asset('admin/js/main.js')}}"></script>
<script src="{{asset('admin/js/custom.js')}}"></script>

<!--Datatable-->
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.1/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.1/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.1/js/buttons.print.min.js"></script>
<!--Datatable Ends-->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="{{asset('admin/js/BsMultiSelect.min.js')}}"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
<script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
  document.querySelectorAll(".dselect-example").forEach(function(el) {
    dselect(el, {
      search: true,
      creatable: true,
      clearable: true,
      maxHeight: "300px",
      size: "sm",
    });
  });

  $("a[id=citadel").click(function(event) {
    event.preventDefault();
    var href = $(this).attr("href");

    Swal.fire({
      background: "#fff  ",
      title: "Sure to Perform Action?",
      text: "Action cannot be Reverted",
      showDenyButton: true,

      confirmButtonText: "Yes, Do It",
      denyButtonText: "Cancel",
      customClass: {
        actions: "my-actions",

        confirmButton: "order-2",
        denyButton: "order-3",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = href;
      } else if (result.isDenied) {}
    });
  });
</script>
</body>

</html>