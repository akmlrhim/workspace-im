<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use App\Models\Project\TaskComment;
use Flux\Flux;

/**
 * Comment and reply handling for the task detail component.
 */
trait ManagesTaskComments
{
    public string $newComment = '';

    public ?int $replyingToCommentId = null;

    public string $replyBody = '';

    public function addComment(): void
    {
        if (! $this->validateWithToast(['newComment' => 'required|min:1|max:5000'], [
            'newComment.required' => 'Komentar wajib diisi.',
            'newComment.max' => 'Komentar maksimal 5000 karakter.',
        ])) {
            return;
        }

        // Verify task exists
        Task::findOrFail($this->taskId);

        TaskComment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'body' => $this->newComment,
        ]);

        $this->reset('newComment');
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Komentar berhasil ditambahkan.', variant: 'success');
    }

    public function deleteComment(int $commentId): void
    {
        TaskComment::where('id', $commentId)->where('user_id', auth()->id())->delete();
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Komentar berhasil dihapus.', variant: 'danger');
    }

    public function startReply(int $parentCommentId): void
    {
        $this->replyingToCommentId = $parentCommentId;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->replyingToCommentId = null;
        $this->replyBody = '';
    }

    public function addReply(): void
    {
        if (! $this->replyingToCommentId) {
            return;
        }

        if (! $this->validateWithToast(['replyBody' => 'required|min:1|max:5000'], [
            'replyBody.required' => 'Balasan wajib diisi.',
            'replyBody.max' => 'Balasan maksimal 5000 karakter.',
        ])) {
            return;
        }

        $parent = TaskComment::where('id', $this->replyingToCommentId)
            ->where('task_id', $this->taskId)
            ->firstOrFail();

        TaskComment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'body' => $this->replyBody,
        ]);

        $this->reset(['replyingToCommentId', 'replyBody']);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Balasan berhasil ditambahkan.', variant: 'success');
    }

    public function deleteReply(int $replyId): void
    {
        TaskComment::where('id', $replyId)
            ->where('task_id', $this->taskId)
            ->whereNotNull('parent_id')
            ->where('user_id', auth()->id())
            ->delete();

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Balasan berhasil dihapus.', variant: 'danger');
    }
}
