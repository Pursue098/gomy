<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

@section('htmlheader')
    @include('layouts.partials.htmlheader')
@show

<body class="fixed-sidebar">
<div id="wrapper" class="">

    @include('layouts.partials.mainheader')

    <!-- Page Wrapper. Contains page content -->
    <div id="page-wrapper" class="gray-bg dashbard-1">

        @include('layouts.partials.contentheader')

        <div class="row">
            <div class="col-lg-12">
                <div class="wrapper wrapper-content">
                    <!-- Your Page Content Here -->
                    @yield('main-content')
                </div>
            </div>

            @include('layouts.partials.footer')
        </div>


    </div><!-- /.content-wrapper -->

</div><!-- ./wrapper -->

@include('layouts.partials.scripts')

@yield('scripts')

</body>
</html>
