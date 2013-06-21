# Démarrage de l'application
$ ->
    application = new Application()
    application.setParameters "magasin", ""

    console.log "Starting application"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

class Application extends Backbone.View
    el: $("#container")
    parameters: null

    initialize: ->
        this.jobsView = new JobsView()

    setParameters: (parameters) ->
        this.parameters = parameters

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# Représente un job
class Job extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null
        status: null

# Représente une tache
class Task extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null
        status: null

class Parameter extends Backbone.Model
    defaults:
        nom: null

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La collection des jobs.
class JobsCollection extends Backbone.Collection
    model: Job
    url: "http://ping.me/app_dev.php/api/v1/jobs.json"

# La collection de taches d'un job.
class TaskCollection extends Backbone.Collection
    model: Task
    id: null

    initialize: (id) ->
        this.id = id

    url: ->
        if @id
            "http://ping.me/app_dev.php/api/v1/tasks/" + @id + ".json"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La vue d'une seule tache
class TaskView extends Backbone.View
    tagName: "li"

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#taskTemplate").html())
        console.log this.model
        this.$el.html(tmpl(this.model.toJSON()))
        this

# La vue des taches d'un job
class TasksView extends Backbone.View
    id: null

    setId: (id) ->
        this.id = id
        this.collection = new TaskCollection(id)
        this.collection.bind "add", this.renderTask, this
        this.collection.fetch().render

    render: ->
        tmpl = _.template($("#tasksTemplate").html())
        tmpl
            "id": @id

    renderTask: (task) ->
        this.collection.add task
        taskView = new TaskView
            model: task
        $("#tasks-" + @id).append(taskView.render().el)

# La vue d'un seul job
class JobView extends Backbone.View
    tagName: "li"
    tasksView: null

    events:
        'click .infos' : 'afficherTasks'

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#jobTemplate").html())
        this.$el.html(tmpl(this.model.toJSON()))
        this

    afficherTasks: (event) ->
        if not this.tasksView?
            id = $(event.currentTarget).closest("div.infos").data("id")
            this.tasksView = new TasksView
            this.tasksView.setId id
            this.$el.after(this.tasksView.render())
            this.$el.toggleClass "open"
        else
            this.$el.next("ul.tasks").remove()
            this.tasksView = null
            this.$el.toggleClass "open"

# La vue de tout les jobs
class JobsView extends Backbone.View
    $el: $("#jobs")

    initialize: ->
        this.collection = new JobsCollection()
        this.collection.bind "add", this.renderJob, this
        this.collection.fetch()

    renderJob: (job) ->
        this.collection.add job
        jobView = new JobView
            model: job
        $("ul.jobs").append(jobView.render().el)



