<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;

class RoomController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Room::class);

        return view('admin.rooms.index', [
            'rooms' => Room::withCount('bookings')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Room::class);

        return view('admin.rooms.form', ['room' => new Room(['active' => true])]);
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        Room::create($this->payload($request));

        return redirect()->route('admin.rooms.index')->with('ok', 'Ruangan ditambahkan.');
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room);

        return view('admin.rooms.form', compact('room'));
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update($this->payload($request));

        return redirect()->route('admin.rooms.index')->with('ok', 'Data ruangan diperbarui.');
    }

    public function toggle(Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update(['active' => ! $room->active]);

        return back()->with('ok', $room->active ? 'Ruangan diaktifkan.' : 'Ruangan dinonaktifkan.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        if ($room->bookings()->exists()) {
            return back()->with('err', 'Ruangan sudah punya riwayat peminjaman, tidak bisa dihapus. Nonaktifkan saja.');
        }

        $room->delete();

        return back()->with('ok', 'Ruangan dihapus.');
    }

    private function payload(RoomRequest $request): array
    {
        return [
            'name' => $request->validated('name'),
            'capacity' => $request->validated('capacity'),
            'facilities' => $request->validated('facilities'),
            'active' => $request->boolean('active'),
        ];
    }
}
