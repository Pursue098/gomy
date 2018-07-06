@extends('layouts.app')

@section('htmlheader_title')
    Edit User
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
          @if(Session::has('permission-deleted'))
              <div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <i class="icon fa fa-check"></i> {{ Session::get('permission-deleted') }}
              </div>
          @endif
          <div class="ibox-title">
            <h5>Permission List</h5>
          </div><br>
              <a class="btn btn-sm btn-primary" href="{{route('permission.create')}}">Add New Permission</a>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Display Name</th>
                  <th>Description</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($permissions as $row)
                  <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->display_name }}</td>
                    <td>{{ $row->description }}</td>
                    <td>
                      <div class="btn-group">
                        <a class="btn btn-primary" href="{{ route('permission.edit', ['id' => $row->id]) }}" class="btn btn-info btn-xs"><i class="fa fa-pencil" title="Edit"></i> </a>
                          <form method="post" class="permissionDeleteForm" action="{{ route('permission.destroy', ['id' => $row->id]) }}" style="display: inline; margin: 10px;">
                              <input name="_method" type="hidden" value="DELETE">
                              {{csrf_field()}}
                              <a id="deletePermission" class="btn btn-danger deletePermission" class="btn btn-danger btn-xs" ><i class="fa fa-trash-o" title="Delete"></i></a>
                          </form>
                      </div>
                    </td>
                  </tr>
                  @endforeach
              </tbody>
            </table>
            {{ $permissions->links() }}
          </div>
       </div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $('.deletePermission').on('click', function(e){
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
                $(this ).closest('form.permissionDeleteForm').submit();
            });
            return false;
        });
    </script>
@endsection
