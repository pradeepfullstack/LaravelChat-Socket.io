@extends('layouts.app')

@section('content')
<div class="container">
   <div id="snippetContent">
      <main class="content">
         <div class="container p-0">
            <div class="card">
               <div class="row g-0">
                  <div class="col-12 col-lg-5 col-xl-3 border-right">
                  @foreach($friends as $friend)
                     <a href="{{route('home',$friend['id'])}}" class="list-group-item list-group-item-action border-0">
                        @if($friend['unread_messages']>0)
                        <div class="badge bg-success float-right">{{$friend['unread_messages']}}</div>
                        @else
                        @endif
                        <div class="d-flex align-items-start">
                           <img src="https://ui-avatars.com/api/?name={{$friend['name']}}" class="rounded-circle mr-1"
                              alt="Vanessa Tucker" width="40" height="40" />
                           <div class="flex-grow-1 ml-3">
                           {{$friend['name']}}
                              <div class="small" id="status_{{$friend['id']}}">
                              @if($friend['is_online'] == 1)
                              <span class="fa fa-circle chat-online"></span> Online</div>
                              @else
                              <span class="fa fa-circle chat-offline"></span> Offline</div>
                              @endif
                           </div>
                        </div>
                     </a>
                     @endforeach
                     <hr class="d-block d-lg-none mt-1 mb-0" />
                  </div>
                  <div class="col-12 col-lg-7 col-xl-9">
                  @if($id)
                     <div class="py-2 px-4 border-bottom d-none d-lg-block">
                        <div class="d-flex align-items-center py-1">
                           <div class="position-relative"><img src="https://ui-avatars.com/api/?name={{$otherUser->name}}"
                                 class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40" /></div>
                           <div class="flex-grow-1 pl-3">
                              <strong>{{$otherUser->name}}</strong>
                              <div class="text-muted small"><em>Typing...</em></div>
                           </div>
                        </div>
                     </div>
                     <div class="position-relative">
                        <div class="chat-messages p-4">
                        @foreach($massages as $message)
                        @if($message['user_id']== Auth::id())
                           <div class="chat-message-right pb-4">
                              <div>
                                 <img src="https://ui-avatars.com/api/?name={{Auth::user()->name}}"
                                    class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40" />
                                 <div class="text-muted small text-nowrap mt-2">{{date("h:iA",strtotime($message['created_at']))}}</div>
                              </div>
                              <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                 <div class="font-weight-bold mb-1">You</div>
                                 {{$message['message']}}
                              </div>
                           </div>
                           @else
                           <div class="chat-message-left pb-4">
                              <div>
                                 <img src="https://ui-avatars.com/api/?name={{$otherUser->name}}"
                                    class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40" />
                                 <div class="text-muted small text-nowrap mt-2">{{date("h:iA",strtotime($message['created_at']))}}</div>
                              </div>
                              <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                 <div class="font-weight-bold mb-1">{{$otherUser->name}}</div>
                                {{$message['message']}}
                              </div>
                           </div> 
                           @endif
                        @endforeach
                        </div>
                     </div>
                     <div class="flex-grow-0 py-3 px-4 border-top">
                     <form id="chat-form">
                        <div class="input-group"><input type="text" class="form-control" id="message-input"
                              placeholder="Type your message" /> <button class="btn btn-primary" type="submit">Send</button></div>
                    </form>
                     </div>
                     @else

                     @endif
                  </div>
               </div>
            </div>
         </div>
      </main>
   </div>
</div>
@endsection

@section('scripts')
<script>
    $(function(){
        var user_id = '{{Auth::id()}}';
        var other_user_id = '{{($otherUser)?$otherUser->id:''}}';
        var otherUserName = '{{($otherUser)?$otherUser->name:''}}';
        var socket = io("http://localhost:3000",{query:{user_id:user_id}});

        $("#chat-form").on("submit",function(e){
            e.preventDefault();
            var message = $("#message-input").val();
            if(message.trim().length == 0){
                $("#message-input").focus();
            }else{
                var data = {
                    user_id:user_id,
                    other_user_id:other_user_id,
                    message:message,
                    otherUserName:otherUserName
                }
                socket.emit('send_message',data);
                $("#message-input").val('');
            }
        })

        socket.on('user_connected',function(data){
            $("#status_"+data).html('<span class="fa fa-circle chat-online"></span> Online');
        });
        socket.on('user_disconnected',function(data){
            $("#status_"+data).html('<span class="fa fa-circle chat-offline"></span> Offline');
        });

        socket.on('receive_message',function(data){
           console.log("receive_message",data);
           if((data.user_id == user_id && data.other_user_id == other_user_id) || (data.user_id == other_id && data.other_user_id == user_id)){
            if(data.user_id == user_id){
               var html = `<div class="chat-message-right pb-4">
                              <div>
                                 <img src="https://ui-avatars.com/api/?name={{Auth::user()->name}}"
                                    class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40" />
                                 <div class="text-muted small text-nowrap mt-2">${data.time}</div>
                              </div>
                              <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                 <div class="font-weight-bold mb-1">You</div>
                                $(data.message)
                              </div>
                           </div>`
            }else{
               var html = `<div class="chat-message-left pb-4">
                              <div>
                                 <img src="https://ui-avatars.com/api/?name={{$otherUser->name}}"
                                    class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40" />
                                 <div class="text-muted small text-nowrap mt-2">${data.time}</div>
                              </div>
                              <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                 <div class="font-weight-bold mb-1">${data.otherUserName}</div>
                                ${data.message}
                              </div>
                           </div> `
            }  
            $(".chat-message").append(html);
            $(".chat-message").animate({scrollTop:$(".chat-message").prop("scrollHeight")},1000);
           }
        })
    })
</script>
@endsection