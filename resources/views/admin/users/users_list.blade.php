@extends('layouts.app')

@section('htmlheader_title')
    Users List
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('success') }}
        </div>
    @endif
    @if(Session::has('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('error') }}
        </div>
    @endif
    @if(Session::has('user-deleted'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('user-deleted') }}
        </div>
    @endif
    <div class="ibox">
        <div class="ibox-title">
            <h5>Users Listing</h5>
        </div><br>
        <a class="btn btn-sm btn-primary" href="{{route('user.create')}}">Add New User</a>
        <hr>
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                           @foreach($user->roles as $r)
                                {{$r->display_name}}
                            @endforeach
                        </td>
                        <td>
                            <div class="btn-group">

                                <a class="btn btn-primary" href="{{ route('user.edit' , [$user]) }}" class="btn btn-info btn-xs"><i class="fa fa-pencil" title="Role"></i> </a>

                                <!--
                                <form method="post" class="userDeleteForm" action="{{ route('user.destroy', [$user]) }}" style="display: inline; margin: 10px;">
                                    <input name="_method" type="hidden" value="DELETE">
                                    {{csrf_field()}}
                                    <a id="deleteUser" class="btn btn-danger deleteUser" class="btn btn-danger btn-xs" ><i class="fa fa-trash-o" title="Delete"></i></a>
                                </form>
                                -->
                            </div>
                        </td>
                    </tr>
               @endforeach
                </tbody>
            </table>
            {{ $users->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $('.deleteUser').on('click', function(e){
            e.preventDefault();
            swal({
                title: 'Are you sure?',
                height: '200px',
                text: "You want to Delete!",
                showCancelButton: true,
                confirmButtonColor: '#1ab394',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Do it!',
                closeOnConfirm: false,
                customClass: 'swal-height',
            }).then((result) => {

                console.log('result: ', result);
                $(this ).closest('form.userDeleteForm').submit();
            });
            return false;
        });
</script>
@endsection
