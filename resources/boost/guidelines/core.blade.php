## ArFlow - Laravel State Machine & Workflow Package

ArFlow provides state machine and workflow management for Eloquent models with guards, actions, and background jobs.

### Key Concepts

**Workflows**: Collections of states and transitions defining model progression stages.

**States**: Current stage of a model (e.g., `pending`, `approved`, `rejected`).

**Transitions**: Rules for state changes with three components:
- **Guards**: Validation conditions (must pass to allow transition)
- **Actions**: Synchronous operations executed during transition
- **Success Jobs**: Asynchronous background tasks dispatched after successful transition

**History Tracking**: Automatic logging to `arflow_state_transitions` table with actor, comment, and metadata.

### Model Setup

Every model using ArFlow must:

1. Use the `HasState` trait
3. Define `supportedWorkflows()` method

@verbatim
<code-snippet name="Basic Model Setup" lang="php">
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Traits\HasState;
use Illuminate\Database\Eloquent\Model;

class Order extends Model implements StateableModelContract
{
    use HasState;

    public static function supportedWorkflows(): array
    {
        return ['order_processing', 'order_refund'];
    }

    // Optional: Override default attribute names
    public static function workflowAttribute(): string
    {
        return 'workflow'; // default
    }

    public static function stateAttribute(): string
    {
        return 'state'; // default
    }

    public static function stateMetadataAttribute(): string
    {
        return 'state_metadata'; // default
    }
}
</code-snippet>
@endverbatim

### Migration Setup

Use the `arflow()` macro to add workflow columns to your migration:

@verbatim
<code-snippet name="Migration with ArFlow Columns" lang="php">
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->decimal('total', 10, 2);
            $table->arflow(); // Adds: workflow, state, state_metadata columns
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
</code-snippet>
@endverbatim

### Workflow Configuration

All workflows are defined in `config/arflow.php`. Below is a comprehensive example showing all features:

#### E-commerce Order Processing Workflow

@verbatim
<code-snippet name="Order Processing Workflow Config" lang="php">
return [
    'workflows' => [
        'order_processing' => [
            'states' => [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded'
            ],
            'initial_state' => 'pending',
            'transitions' => [
                'confirm_order' => [
                    'from' => ['pending'],
                    'to' => 'confirmed',
                    'guards' => [
                        [PaymentVerificationGuard::class, ['verify_payment' => true]],
                        [InventoryCheckGuard::class],
                    ],
                    'actions' => [
                        [SendConfirmationEmailAction::class],
                        [ReserveInventoryAction::class],
                    ],
                    'success_metadata' => ['confirmed_at' => now()],
                    'success_jobs' => [
                        [GenerateInvoiceJob::class, ['send_email' => true]],
                        [NotifyWarehouseJob::class],
                    ],
                ],
                'start_processing' => [
                    'from' => ['confirmed'],
                    'to' => 'processing',
                    'guards' => [
                        [WarehouseReadyGuard::class],
                    ],
                    'actions' => [
                        [UpdateInventoryAction::class],
                    ],
                    'success_jobs' => [
                        [NotifyCustomerJob::class, ['template' => 'processing']],
                    ],
                ],
                'ship_order' => [
                    'from' => ['processing'],
                    'to' => 'shipped',
                    'guards' => [
                        [TrackingNumberGuard::class, ['required' => true]],
                    ],
                    'actions' => [
                        [GenerateShippingLabelAction::class],
                        [UpdateShippingAction::class],
                    ],
                    'success_jobs' => [
                        [SendTrackingEmailJob::class],
                    ],
                ],
                'mark_delivered' => [
                    'from' => ['shipped'],
                    'to' => 'delivered',
                    'guards' => [],
                    'actions' => [
                        [CompleteOrderAction::class],
                    ],
                    'success_jobs' => [
                        [SendDeliveryConfirmationJob::class],
                        [RequestReviewJob::class, ['delay' => 3600]],
                    ],
                ],
                'cancel_order' => [
                    'from' => ['pending', 'confirmed', 'processing'],
                    'to' => 'cancelled',
                    'guards' => [
                        [CancellationPermissionGuard::class, ['refund_eligible' => true]],
                    ],
                    'actions' => [
                        [ReleaseInventoryAction::class],
                        [ProcessRefundAction::class],
                    ],
                    'success_jobs' => [
                        [SendCancellationEmailJob::class],
                        [NotifyFinanceJob::class],
                    ],
                ],
            ],
        ],
    ],
];
</code-snippet>
@endverbatim

#### Simple Toggle Workflow (Minimal Example)

@verbatim
<code-snippet name="Simple Toggle Workflow Config" lang="php">
// Simplest possible workflow - on/off toggle
'feature_toggle' => [
    'states' => ['disabled', 'enabled'],
    'initial_state' => 'disabled',
    'transitions' => [
        'enable' => [
            'from' => ['disabled'],
            'to' => 'enabled',
            'guards' => [[PermissionGuard::class, ['permission' => 'manage_features']]],
            'actions' => [[EnableFeatureAction::class]],
            'success_jobs' => [[ClearCacheJob::class]],
        ],
        'disable' => [
            'from' => ['enabled'],
            'to' => 'disabled',
            'guards' => [[PermissionGuard::class, ['permission' => 'manage_features']]],
            'actions' => [[DisableFeatureAction::class]],
            'success_jobs' => [[ClearCacheJob::class]],
        ],
    ],
],
</code-snippet>
@endverbatim

### Creating Transition Guards

Guards implement `TransitionGuardContract` and return `TransitionGuardResultDTO` with `ALLOWED` or `DISALLOWED` status:

@verbatim
<code-snippet name="Guard Implementation Example" lang="php">
namespace App\ArFlow\Guards;

use AuroraWebSoftware\ArFlow\Contacts\{StateableModelContract, TransitionGuardContract};
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use Illuminate\Database\Eloquent\Model;

class PaymentVerificationGuard implements TransitionGuardContract
{
    private StateableModelContract&Model $model;
    private string $from;
    private string $to;
    private array $parameters;

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters): void
    {
        $this->model = $model;
        $this->from = $from;
        $this->to = $to;
        $this->parameters = $parameters;
    }

    public function handle(): TransitionGuardResultDTO
    {
        // Check multiple conditions
        if ($this->model->payment_status !== 'paid') {
            return TransitionGuardResultDTO::build(
                TransitionGuardResultDTO::DISALLOWED,
                ['Payment not confirmed']
            );
        }

        if ($this->parameters['verify_amount'] ?? false) {
            if ($this->model->paid_amount < $this->model->total) {
                return TransitionGuardResultDTO::build(
                    TransitionGuardResultDTO::DISALLOWED,
                    ['Insufficient payment amount']
                );
            }
        }

        return TransitionGuardResultDTO::build(TransitionGuardResultDTO::ALLOWED);
    }
}
</code-snippet>
@endverbatim

### Creating Transition Actions

Actions implement `TransitionActionContract` and execute synchronously during transition. Must implement `handle()` and `failed()` methods:

@verbatim
<code-snippet name="Action Implementation Example" lang="php">
namespace App\ArFlow\Actions;

use AuroraWebSoftware\ArFlow\Contacts\{StateableModelContract, TransitionActionContract};
use Illuminate\Database\Eloquent\Model;

class ReserveInventoryAction implements TransitionActionContract
{
    private StateableModelContract&Model $model;
    private string $from;
    private string $to;
    private array $parameters;

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        $this->model = $model;
        $this->from = $from;
        $this->to = $to;
        $this->parameters = $parameters;
    }

    public function handle(): void
    {
        foreach ($this->model->orderItems as $item) {
            $product = $item->product;

            // Reserve inventory
            $product->decrement('available_quantity', $item->quantity);
            $product->increment('reserved_quantity', $item->quantity);

            // Update model metadata
            $this->model->state_metadata = array_merge(
                $this->model->state_metadata ?? [],
                ['inventory_reserved_at' => now()]
            );
        }
    }

    public function failed(): void
    {
        \Log::error('Inventory reservation failed', [
            'order_id' => $this->model->id,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }
}
</code-snippet>
@endverbatim

### Creating Success Jobs

Jobs extend `AbstractTransitionSuccessJob` and run asynchronously after successful transition. Access `$this->model`, `$this->from`, `$this->to`, and `$this->parameters`:

@verbatim
<code-snippet name="Success Job Example" lang="php">
namespace App\ArFlow\Jobs;

use AuroraWebSoftware\ArFlow\Abstracts\AbstractTransitionSuccessJob;
use Illuminate\Support\Facades\Mail;

class SendConfirmationEmailJob extends AbstractTransitionSuccessJob
{
    public function handle(): void
    {
        // Parent class provides: $this->model, $this->from, $this->to, $this->parameters

        $template = $this->parameters['template'] ?? 'order_confirmed';

        Mail::to($this->model->customer->email)->send(
            new OrderNotificationMail($this->model, [
                'previous_state' => $this->from,
                'current_state' => $this->to,
                'template' => $template,
            ])
        );
    }
}
</code-snippet>
@endverbatim

### Usage Examples

#### Applying Workflow and Performing Transitions

@verbatim
<code-snippet name="Basic Usage" lang="php">
// 1. Create model and apply workflow
$order = Order::create(['order_number' => 'ORD-001', 'total' => 99.99]);
$order->applyWorkflow('order_processing'); // Sets to initial_state 'pending'

// 2. Check current state
$order->currentState(); // 'pending'
$order->currentWorkflow(); // 'order_processing'

// 3. Check allowed transitions
if ($order->canTransitionTo('confirmed')) {
    // Perform transition with full parameters
    $order->transitionTo(
        toState: 'confirmed',
        comment: 'Payment verified',
        actorModelType: User::class,
        actorModelId: auth()->id(),
        metadata: ['approved_by' => auth()->user()->name],
        logHistoryTransitionAction: true
    );
}

// 4. Get available transitions
$order->definedTransitionStates(); // ['confirmed', 'cancelled'] - all defined
$order->allowedTransitionStates(); // ['confirmed'] - only if guards pass
</code-snippet>
@endverbatim

#### Advanced Usage: Bypassing Guards and History

@verbatim
<code-snippet name="Advanced Transitions" lang="php">
// Skip specific guard (admin override)
$order->transitionTo('shipped', withoutGuards: [TrackingNumberGuard::class]);

// Skip all guards
$order->transitionTo('shipped', withoutGuards: ['*']);

// Access transition history
use AuroraWebSoftware\ArFlow\StateTransition;

$history = StateTransition::where([
    'workflow' => 'order_processing',
    'model_type' => Order::class,
    'model_id' => $order->id,
])->orderBy('created_at', 'desc')->get();

// Get detailed guard results
$guardResults = $order->transitionGuardResults('confirmed');
</code-snippet>
@endverbatim

### Advanced Features

@verbatim
<code-snippet name="Multiple Workflows and Custom Attributes" lang="php">
// 1. Model with multiple workflows
class Order extends Model implements StateableModelContract
{
    use HasState;

    public static function supportedWorkflows(): array
    {
        return ['order_processing', 'order_refund', 'order_return'];
    }

    // 2. Custom attribute names (optional)
    public static function workflowAttribute(): string { return 'order_workflow'; }
    public static function stateAttribute(): string { return 'order_status'; }
    public static function stateMetadataAttribute(): string { return 'status_metadata'; }
}

// Switch between workflows
$order->applyWorkflow('order_processing');
$order->applyWorkflow('order_refund'); // Later

// 3. Multiple from/to states in config
'cancel_from_any' => [
    'from' => ['pending', 'confirmed', 'processing'],
    'to' => 'cancelled',
    'guards' => [[CancellationPermissionGuard::class]],
],
</code-snippet>
@endverbatim

### Important Notes

- **Always call `applyWorkflow()`** before using state methods
- **Never manually set state** - use `transitionTo()` instead
- **Check guards first** with `canTransitionTo()` for better UX
- **Actions are synchronous** - use for operations that must complete before transition
- **Jobs are asynchronous** - use for heavy operations like emails, notifications
- **Implement `failed()`** in actions for proper error handling
- **Use descriptive names** for transitions (e.g., `confirm_order` not `transition1`)

