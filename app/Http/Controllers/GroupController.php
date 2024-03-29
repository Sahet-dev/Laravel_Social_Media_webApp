<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserRole;
use App\Http\Enums\GroupUserStatus;
use App\Http\Requests\InviteUsersRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Http\Resources\PostAttachmentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationInGroup;
use App\Notifications\RequestApproved;
use App\Notifications\RequestToJoinGroup;
use App\Notifications\RoleChanged;
use App\Notifications\UserRemovedFromGroup;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;


class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function profile(Group $group, Request $request)
    {
        $group->load('currentUserGroup');

        $userId = Auth::id();

        if ($group->isApprovedUser($userId)){
            $posts = Post::query()
                ->withCount('reactions')
                ->with([
                    'comments' => function ($query) {
                        $query->withCount('reactions');
                    },
                    'reactions' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }])
                ->where('group_id', $group->id)
                ->latest()->paginate(8);
                $posts = PostResource::collection($posts);
        }else{
            $posts = null;
            return Inertia::render('Group/View', [
                'success' => session('success'),
                'group' => new GroupResource($group),
                'posts' => $posts,
                'users' => [],
                'requests' => []
            ]);
        }


        if ($request->wantsJson()){
            return $posts;
        }

        $users = User::query()
            ->select(['users.*', 'gu.role', 'gu.status', 'gu.group_id'])
            ->join('group_users AS gu', 'gu.user_id', 'users.id')
            ->orderBy('users.name')
            ->where('group_id', $group->id)
            ->get();
        $requests = $group->pendingUsers()->orderBy('name')->get();


        $photos = PostAttachment::query()
            ->select('post_attachments.*', 'p.group_id')
            ->join('posts AS p', 'p.id', 'post_attachments.post_id')
            ->where('p.group_id', $group->id)
            ->where('mime', 'like', 'image/%')
            ->latest()
            ->get();



        return Inertia::render('Group/View', [
            'success' => session('success'),
            'group' => new GroupResource($group),
            'posts' => $posts,
            'users' => GroupUserResource::collection($users),
            'requests' => UserResource::collection($requests),
            'photos' => PostAttachmentResource::collection($photos),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $group = Group::create($data);

        $groupUserData = [
            'status'=> GroupUserStatus::APPROVED->value,
            'role'=> GroupUserRole::ADMIN->value,
            'user_id' => Auth::id(),
            'group_id' => $group->id,
            'created_by' => Auth::id(),
        ];

        GroupUser::create($groupUserData);
        $group->status = $groupUserData['status'];
        $group->role = $groupUserData['role'];

        return response(new GroupResource($group), 201);

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->validated());

        return back()->with('success', "Group was updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        //
    }

    public function updateImage(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())){
            return response("Access Denied", 403);
        }
        $data = $request->validate([
            'cover'=> ['nullable', 'image'],
            'thumbnail'=> ['nullable', 'image']
        ]);

        $thumbnail = $data['thumbnail'] ?? null;
        /**@var UploadedFile $cover*/
        $cover = $data['cover'] ?? null;

        $success = '';
        if ($cover){
            if ($group->cover_path){
                Storage::disk('public')->delete($group->cover_path);
            }
            $path = $cover->store('group-'.$group->id, 'public');
            $group->update(['cover_path'=> $path]);
            $success = 'Your new Cover Image has been saved.';
        }

        if ($thumbnail){
            if ($group->thumbnail_path){
                Storage::disk('public')->delete($group->thumbnail_path);
            }
            $path = $thumbnail->store('group-'.$group->id, 'public');
            $group->update(['thumbnail_path'=> $path]);
            $success = 'Your new thumbnail Image has been saved.';
        }


        session('success', 'Cover Image saved');
        return back()->with('success', $success);
    }

    public function inviteUsers(InviteUsersRequest $request, Group $group)
    {
        $data = $request->validated();
        $user = $request->user;

//        $groupUser = GroupUser::where('user_id', Auth::id())->where('group_id', $group->id)->first();

//        if ($groupUser && $groupUser->status === GroupUserStatus::APPROVED->value){
//            return back()->with('error', 'User is already joined to the Group');
//        }




        $groupUser = $request->groupUser;

        if ($groupUser){
            $groupUser->delete();
        }

        $hours= 24;
        $token = Str::random(256);

        GroupUser::create([
            'status' => GroupUserStatus::PENDING->value,
            'role' => GroupUserRole::USER->value,
            'token' => $token,
            'token_expired_date' => Carbon::now()->addHours($hours),
            'user_id' => $user->id,
            'group_id' => $group->id,
            'created_by' => Auth::id(),
            ]);


        $user->notify(new InvitationInGroup($group, $hours, $token));

        return back()->with('success', 'User was invited to join Group');
    }

    public function approveInvitation(string $token)
    {
        $groupUser = GroupUser::query()
            ->where('token', $token)
            ->first();
        $errorTitle = '';
        if (!$groupUser) {
            $errorTitle = 'Link is not valid';
        } else if ($groupUser->token_used || $groupUser->status === GroupUserStatus::APPROVED->value) {
            $errorTitle = 'The link is already used';
        } else if ($groupUser->token_expire_date < Carbon::now()) {
            $errorTitle = 'Invitation link is expired';
        }
        if ($errorTitle) {
            return \inertia('Error', compact('errorTitle'));
        }

        $groupUser->status = GroupUserStatus::APPROVED->value;
        $groupUser->token_used = Carbon::now();
        $groupUser->save();

        $adminUser = $groupUser->adminUser;

        $adminUser->notify(new InvitationApproved($groupUser->group, $groupUser->user));

        return redirect(route('group.profile', $groupUser->group))
            ->with('success', 'You accepted to join to group"'.$groupUser->group->name.'"');
    }

    public function join(Group $group)
    {
        $user  = \request()->user();

        $status = GroupUserStatus::APPROVED->value;
        $successMessage = 'You have joined to group "'. $group->name .'"';
        if (!$group->auto_approval){
            $status = GroupUserStatus::PENDING->value;
            Notification::send($group->adminUsers, new RequestToJoinGroup($group, $user));
            $successMessage = 'Your request has been accepted. You will be notified once you will be approved. ';
        }


        GroupUser::create([
            'status' => $status,
            'role' => GroupUserRole::USER->value,
            'user_id' => $user->id,
            'group_id' => $group->id,
            'created_by' => $user->id,
        ]);

        return back()->with('success', $successMessage);
    }



    public function approveRequest(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())){
            return response("Access Denied", 403);
        }

        $data = $request->validate([
            'user_id' => ['required'],
            'action' => ['required']
        ]);

        $groupUser = GroupUser::where('user_id', $data['user_id'])
            ->where('group_id', $group->id)
            ->where('status', GroupUserStatus::PENDING->value)
            ->first();

        if ($groupUser){
            $approved = false;
            if ($data['action'] === 'approve'){
                $approved = true;
                $groupUser->status = GroupUserStatus::APPROVED->value;
            }else{
                $groupUser->status = GroupUserStatus::REJECTED->value;

            }
            $groupUser->save();

            $user = $groupUser->user;
            $user->notify(new RequestApproved($groupUser->group, $user, $approved));

            return back()->with('success', 'User "'.$groupUser->user->name.'" was '.($approved ? 'approved' : 'rejected' ));
        }
        return back();

    }

    public function removeUser(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())){
            return response("Access Denied", 403);
        }

        $data = $request->validate([
            'user_id' => ['required'],
        ]);


        $user_id = $data['user_id'];

        if($group->isOwner($user_id)){
            return response('Permission Denied! This user is owner of the group.', 403);
        }
        $groupUser = GroupUser::where('user_id', $user_id)
            ->where('group_id', $group->id)
            ->first();

        if ($groupUser){
            $user = $groupUser->user;
            $groupUser->delete();

            $user->notify(new UserRemovedFromGroup($group));
        }
        return back();
    }

    public function changeRole(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())){
            return response("Access Denied", 403);
        }

        $data = $request->validate([
            'user_id' => ['required'],
            'role' => ['required', Rule::enum(GroupUserRole::class)]
        ]);


        $user_id = $data['user_id'];

        if($group->isOwner($user_id)){
            return response('Permission Denied', 403);
        }
        $groupUser = GroupUser::where('user_id', $data['user_id'])
            ->where('group_id', $group->id)
            ->first();

        if ($groupUser){
            $groupUser->role = $data['role'];
            $groupUser->save();

            $groupUser->user->notify(new RoleChanged($group, $data['role']));

        }
        return back();

    }

}
