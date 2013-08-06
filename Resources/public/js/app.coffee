baseUrl = "http://ping.me/app_dev.php"

# Démarrage de l'application
$ ->
    application = new Application(new Date)

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

class Application extends Backbone.View
    currentDate: null
    el: "#container"

    events:
        'click div.nextDay a' : 'loadNextDay'

    loadNextDay: (event) ->
        event.preventDefault()
        event.stopPropagation()
        this.currentDate = moment(moment(this.currentDate)).add('days', -1).toDate()
        new JobsView(this.currentDate)

    initialize: (date) ->
        this.currentDate = date
        new JobsView(this.currentDate)

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# Représente un job
class Job extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        lastStatus: null
        globalStatus: null

# Représente une tache
class Task extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null
        startedBy: null
        status: null
        message: null

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La collection des jobs.
class JobsCollection extends Backbone.Collection
    model: Job
    date: null

    initialize: (date) ->
        this.date = date
        
    url: ->
        if this.date != null
            baseUrl + "/api/v1/jobs/" + this.date.getFullYear() + "/" + (this.date.getMonth() + 1) + "/" + this.date.getDate() + "/list.json"
        else
            throw "The date has to be defined"

# La collection de taches d'un job.
class TaskCollection extends Backbone.Collection
    model: Task
    id: null

    initialize: (id) ->
        this.id = id

    url: ->
        if this.id
            baseUrl + "/api/v1/tasks/" + @id + ".json"
        else
            throw "The ID has to be defined"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La vue d'une seule tache
class TaskView extends Backbone.View
    tagName: "li"

    events:
        'click .message' : 'showMessage'

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#taskTemplate").html())
        this.$el.html(tmpl(this.model.toJSON()))
        this

    showMessage: ->
        alert(this.model.toJSON().message)

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
    date: null

    initialize: (date) ->
        this.date = date
        this.collection = new JobsCollection(date)
        this.collection.bind "add", this.renderJob, this
        this.collection.fetch
            success: (collection, result) =>
                if (collection.length == 0)
                    $("#" + this.cid + " ul.jobs").append($("#jobTemplateEmpty").html())
            , this
        this.render()

    render: ->
        data =
            "date" : this.date
            "cid" : this.cid
        tmpl = _.template($("#dayTemplate").html())
        $("#container div.nextDay").before(tmpl(data))

    renderJob: (job) ->
        this.collection.add job
        jobView = new JobView
            model: job
        $("#" + this.cid + " ul.jobs").append(jobView.render().el)