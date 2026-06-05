@extends('layouts.app')
@section('title', 'Maintenance Équipements')

@section('content')
<div class="space-y-5" x-data="{ showNewLog: false }">

    <div class="flex items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">Suivi des pannes et interventions techniques</p>
        </div>
        <button @click="showNewLog = true"
                class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i> Signaler une panne
        </button>
        <a href="{{ route('equipment.index') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-dark px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50">
            <i data-lucide="tool" class="w-4 h-4"></i> Équipements
        </a>
    </div>

    {{-- Liste interventions --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Équipement</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Problème</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Type</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Coût</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs ?? [] as $log)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-semibold text-dark">{{ $log->equipment->name }}</p>
                                <p class="text-xs text-gray-400">{{ $log->equipment->getTypeLabel() }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-dark">{{ $log->title }}</p>
                                <p class="text-xs text-gray-400 truncate max-w-48">{{ Str::limit($log->description, 60) }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="badge-status {{ match($log->type) {
                                    'urgence'   => 'bg-red-50 text-red-700',
                                    'corrective'=> 'bg-orange-50 text-orange-700',
                                    default     => 'bg-blue-50 text-blue-700',
                                } }}">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="badge-status {{ match($log->status) {
                                    'resolu'  => 'bg-green-50 text-green-700',
                                    'en_cours'=> 'bg-yellow-50 text-yellow-700',
                                    'annule'  => 'bg-gray-50 text-gray-500',
                                    default   => 'bg-red-50 text-red-700',
                                } }}">
                                    {{ match($log->status) { 'signale'=>'Signalé','en_cours'=>'En cours','resolu'=>'Résolu','annule'=>'Annulé',default=>$log->status } }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-medium text-dark">
                                {{ $log->cost > 0 ? number_format($log->cost, 0, ',', ' ') . ' FCFA' : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-xs text-gray-400">
                                {{ $log->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($log->status !== 'resolu')
                                    <form method="POST" action="{{ route('maintenance.resolve', $log) }}" class="inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Marquer résolu">
                                            <i data-lucide="check-circle" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-3 text-green-300"></i>
                                <p class="font-medium">Aucune intervention signalée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal nouvelle panne --}}
    <div x-show="showNewLog" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div @click.outside="showNewLog = false" class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="font-display font-bold text-dark text-lg mb-4">Signaler une panne / intervention</h3>
            <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1">Équipement *</label>
                    <select name="equipment_id" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        @foreach($equipment ?? [] as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->getTypeLabel() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-dark mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm">
                            <option value="corrective">Corrective</option>
                            <option value="urgence">Urgence</option>
                            <option value="preventive">Préventive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-dark mb-1">Date prévue</label>
                        <input type="date" name="scheduled_date" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1">Titre *</label>
                    <input type="text" name="title" required placeholder="Ex: Moteur bloqué..."
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1">Description *</label>
                    <textarea name="description" required rows="3" placeholder="Décrivez le problème..."
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showNewLog = false"
                            class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold">Annuler</button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary-600">Signaler</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
