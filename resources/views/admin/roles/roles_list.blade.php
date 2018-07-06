@extends('layouts.app')

@section('htmlheader_title')
    Roles Listiing
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    <div class="ibox">
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fa fa-check"></i> {{ Session::get('success') }}
            </div>
        @endif
        @if(Session::has('role-deleted'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fa fa-check"></i> {{ Session::get('role-deleted') }}
            </div>
        @endif
        <div class="ibox-title">
            <h5>Roles</h5>
        </div><br>
            <a class="btn btn-sm btn-primary" href="{{route('roles.create')}}">Add New Role</a>
        <hr>
        <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>Role Display</th>
                  <th>Role Description</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->display_name }}</td>
                        <td>{{ $role->description }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                          <div class="btn-group">
                            <a class="btn btn-primary" href="{{ route('roles.edit', ['id' => $role->id]) }}" class="btn btn-info btn-xs"><i class="fa fa-pencil" title="Edit"></i> </a>

                              <form method="post" class="roleDeleteForm" action="{{ route('roles.destroy', ['id' => $role->id]) }}" style="display: inline; margin: 10px;">
                                  <input name="_method" type="hidden" value="DELETE">
                                  {{csrf_field()}}
                                  <a id="deleteRole" class="btn btn-danger deleteRole" class="btn btn-danger btn-xs" ><i class="fa fa-trash-o" title="Delete"></i></a>
                              </form>
                          </div>
                        </td>
                    </tr>
                @endforeach
              </tbody>
            </table>
            {{ $roles->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $('.deleteRole').on('click', function(e){
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
                $(this ).closest('form.roleDeleteForm').submit();

            });
            return false;
        });
    </script>
@endsection