<?php

namespace Tests\Unit\Repositories;

use App\Models\Room;
use App\Models\User;
use App\Repositories\RoomRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(RoomRepository::class)]
class RoomRepositoryTest extends TestCase
{
    private RoomRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(RoomRepository::class);
    }

    #[Test]
    public function find_by_id_returns_room(): void
    {
        // Arrange
        $room = Room::factory()->create();

        // Act
        $found = $this->repository->findById($room->id);

        // Assert
        $this->assertInstanceOf(Room::class, $found);
        $this->assertEquals($room->id, $found->id);
    }

    #[Test]
    public function find_by_id_returns_null_if_not_found(): void
    {
        // Act
        $found = $this->repository->findById(99999);

        // Assert
        $this->assertNull($found);
    }

    #[Test]
    public function find_by_code_returns_room(): void
    {
        // Arrange
        $room = Room::factory()->create(['code' => 'TESTCODE']);

        // Act
        $found = $this->repository->findByCode('TESTCODE');

        // Assert
        $this->assertInstanceOf(Room::class, $found);
        $this->assertEquals('TESTCODE', $found->code);
    }

    #[Test]
    public function find_by_code_returns_null_if_not_found(): void
    {
        // Act
        $found = $this->repository->findByCode('NOTEXIST');

        // Assert
        $this->assertNull($found);
    }

    #[Test]
    public function create_stores_room(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Room',
            'code' => 'NEWCODE',
            'host_id' => User::factory()->create()->id,
        ];

        // Act
        $room = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(Room::class, $room);
        $this->assertEquals('Test Room', $room->name);
        $this->assertDatabaseHas('rooms', ['name' => 'Test Room']);
    }

    #[Test]
    public function update_modifies_room(): void
    {
        // Arrange
        $room = Room::factory()->create(['name' => 'Old Name']);

        // Act
        $updated = $this->repository->update($room, ['name' => 'New Name']);

        // Assert
        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'New Name']);
    }

    #[Test]
    public function delete_removes_room(): void
    {
        // Arrange
        $room = Room::factory()->create();
        $id = $room->id;

        // Act
        $deleted = $this->repository->delete($room);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('rooms', ['id' => $id]);
    }

    #[Test]
    public function paginate_for_user_returns_hosted_and_member_rooms(): void
    {
        // Arrange
        $user = User::factory()->create();
        $hostedRoom = Room::factory()->create(['host_id' => $user->id]);
        $memberRoom = Room::factory()->create();
        $memberRoom->users()->attach($user->id, ['role' => 'voter']);
        Room::factory()->create(); // Room where user is not involved

        // Act
        $paginated = $this->repository->paginateForUser($user->id);

        // Assert
        $this->assertGreaterThanOrEqual(2, $paginated->total());
        $ids = $paginated->pluck('id')->toArray();
        $this->assertContains($hostedRoom->id, $ids);
        $this->assertContains($memberRoom->id, $ids);
    }
}
