
  <table class="staffList subscription-table responsive-table highlight">
    <tbody>
      <!-- <tr>
        <td class="badge green lighten-5 green-text saveEdit"><span class="saveh blue white-text">Edit</span><i class="material-icons green-text">check</i></td>
        <td class="badge yellow lighten-5 yellow-text text-accent-4 saveEdit"><span class="saveh blue white-text">Edit</span>Out</td>
      </tr> -->
      @foreach($staffs as $staff)
        <tr>
          <td>{{++$loop->index}}</td>
          <td><a href="#"> {{$staff->user->name}} </a></td>
            @forelse ($staff[$staff->user_id] as $attendance)
              
              
              <td class="badge green lighten-5 green-text saveEdit ">
                  @php 
                  $editClass    = 'edit-markings';
                  $displayText  = 'Edit';
                  $inTime       = new DateTime($attendance->in_time);
                  @endphp

                  @if($loop->last)

                    @php
                      if($attendance->out_time == null){
                        $editClass = '';
                        $displayText  = 'In: ' . date('h:i:s A', strtotime($attendance->in_time));
                      }
                    @endphp

                  @endif
                  
                <span class="saveh blue white-text @if($editable == 1) {{$editClass}} @endif" data-time="{{$inTime->format('h:i A')}}" data-id="{{$attendance->id}}" data-staffId="{{$staff->user_id}}" data-action="inTime">
                  @if($editable == 1)
                    {{$displayText}} 
                  @else
                    In: @php echo date('h:i:s A', strtotime($attendance->in_time)) @endphp
                  @endif
                </span>
              
                In: @php echo date('h:i:s A', strtotime($attendance->in_time)) @endphp
            
            </td>
              @if($attendance->out_time != null) 
                  @php 
                    $outTime    = new DateTime($attendance->out_time);
                    $interval   = $inTime->diff($outTime);
                  @endphp
                  <td class="badge yellow lighten-5 yellow-text text-accent-4 saveEdit">

                    <span class="saveh blue white-text @if($editable == 1) edit-markings @endif" data-time="{{$outTime->format('h:i A')}}" data-id="{{$attendance->id}}" data-action="outTime">
                    @if($editable == 1)
                      Edit 
                    @else
                      {{$interval->format('%h')." Hours ".$interval->format('%i')." Minutes"}}
                    @endif
                  </span>
                    
                    Out @php echo date('h:i:s A', strtotime($attendance->out_time)) @endphp</td>
              @endif
              
            @empty
              <td class="badge lighten-5 ">No Record</td>
            @endforelse



        </tr>
      @endforeach
    </tbody>
  </table>
                  